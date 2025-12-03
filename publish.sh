#!/bin/bash

# AISEO / Praison AI SEO - WordPress.org SVN Publisher
# This script publishes the plugin to WordPress.org SVN repositories
#
# Usage: ./publish.sh [version] [--dry-run]
# Example: ./publish.sh 5.0.4
#          ./publish.sh 5.0.4 --dry-run

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SVN_USERNAME="mervinpraison"

# SVN Repositories - Add more as needed
declare -A SVN_REPOS
SVN_REPOS["seo-wordpress"]="/home/mervin/wordpress-plugins/seo-wordpress-svn"
# SVN_REPOS["aiseo"]="/home/mervin/wordpress-plugins/aiseo-svn"  # Uncomment when ready

# Main plugin file mapping for each repo
declare -A MAIN_FILES
MAIN_FILES["seo-wordpress"]="seo-wordpress.php"
# MAIN_FILES["aiseo"]="aiseo.php"  # Uncomment when ready

# Plugin names for readme.txt header
declare -A PLUGIN_NAMES
PLUGIN_NAMES["seo-wordpress"]="Praison AI SEO"
# PLUGIN_NAMES["aiseo"]="AISEO"  # Uncomment when ready

# Files/folders to exclude from SVN
EXCLUDES=(
    '.git'
    'node_modules'
    'tests'
    '.env'
    '*.md'
    '.distignore'
    '.gitignore'
    'add-logging.js'
    'api-index.jsonl'
    'publish.sh'
)

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to get current version from seo-wordpress.php
get_current_version() {
    grep -oP "Version:\s*\K[0-9]+\.[0-9]+\.[0-9]+" "$PLUGIN_DIR/seo-wordpress.php" | head -1
}

# Function to validate version format
validate_version() {
    if [[ ! $1 =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        print_error "Invalid version format: $1. Expected format: X.Y.Z"
        exit 1
    fi
}

# Function to update version in files
update_version() {
    local new_version=$1
    local current_version=$(get_current_version)
    
    print_status "Updating version from $current_version to $new_version..."
    
    # Update aiseo.php
    sed -i "s/Version: $current_version/Version: $new_version/" "$PLUGIN_DIR/aiseo.php"
    sed -i "s/define('AISEO_VERSION', '$current_version')/define('AISEO_VERSION', '$new_version')/" "$PLUGIN_DIR/aiseo.php"
    
    # Update seo-wordpress.php
    sed -i "s/Version: $current_version/Version: $new_version/" "$PLUGIN_DIR/seo-wordpress.php"
    sed -i "s/define('AISEO_VERSION', '$current_version')/define('AISEO_VERSION', '$new_version')/" "$PLUGIN_DIR/seo-wordpress.php"
    
    # Update readme.txt
    sed -i "s/Stable tag: $current_version/Stable tag: $new_version/" "$PLUGIN_DIR/readme.txt"
    
    print_success "Version updated to $new_version"
}

# Function to build rsync exclude arguments
build_excludes() {
    local excludes=""
    for exclude in "${EXCLUDES[@]}"; do
        excludes="$excludes --exclude='$exclude'"
    done
    echo "$excludes"
}

# Function to publish to a single SVN repo
publish_to_svn() {
    local repo_name=$1
    local svn_dir=$2
    local main_file=$3
    local version=$4
    local dry_run=$5
    local plugin_name=$6
    
    print_status "Publishing to $repo_name ($plugin_name)..."
    
    # Check if SVN directory exists
    if [ ! -d "$svn_dir" ]; then
        print_warning "SVN directory not found: $svn_dir. Skipping $repo_name."
        return 1
    fi
    
    # Sync files to SVN trunk
    print_status "Syncing files to $svn_dir/trunk/..."
    
    local excludes=$(build_excludes)
    eval rsync -av $excludes --delete "$PLUGIN_DIR/" "$svn_dir/trunk/"
    
    # Remove the other main file (keep only the one for this repo)
    if [ "$main_file" == "seo-wordpress.php" ]; then
        rm -f "$svn_dir/trunk/aiseo.php"
    elif [ "$main_file" == "aiseo.php" ]; then
        rm -f "$svn_dir/trunk/seo-wordpress.php"
    fi
    
    # Remove any remaining dev files
    rm -f "$svn_dir/trunk/.distignore" \
          "$svn_dir/trunk/.gitignore" \
          "$svn_dir/trunk/add-logging.js" \
          "$svn_dir/trunk/api-index.jsonl" \
          "$svn_dir/trunk/publish.sh"
    
    # Update plugin name in readme.txt for this repo
    print_status "Updating readme.txt plugin name to: $plugin_name"
    sed -i "s/^=== .* ===$/=== $plugin_name ===/" "$svn_dir/trunk/readme.txt"
    
    # Update first line description to match plugin name
    sed -i "s/^AISEO is a powerful/$plugin_name is a powerful/" "$svn_dir/trunk/readme.txt"
    sed -i "s/^Praison AI SEO is a powerful/$plugin_name is a powerful/" "$svn_dir/trunk/readme.txt"
    
    # Check SVN status
    cd "$svn_dir"
    
    # Add new files
    svn status | grep '^\?' | awk '{print $2}' | xargs -r svn add
    
    # Remove deleted files
    svn status | grep '^\!' | awk '{print $2}' | xargs -r svn rm
    
    local changes=$(svn status | wc -l)
    
    if [ "$changes" -eq 0 ]; then
        print_warning "No changes to commit for $repo_name"
        return 0
    fi
    
    print_status "Changes detected: $changes files"
    svn status
    
    if [ "$dry_run" == "true" ]; then
        print_warning "[DRY RUN] Would commit to $repo_name trunk and create tag $version"
        return 0
    fi
    
    # Get SVN password from environment or prompt
    if [ -z "$SVN_PASSWORD" ]; then
        echo -n "Enter SVN password for $SVN_USERNAME: "
        read -s SVN_PASSWORD
        echo
    fi
    
    # Commit trunk
    print_status "Committing trunk..."
    svn ci -m "Version $version" --username "$SVN_USERNAME" --password "$SVN_PASSWORD"
    
    # Create tag
    print_status "Creating tag $version..."
    svn cp trunk "tags/$version"
    svn ci -m "Tagging version $version" --username "$SVN_USERNAME" --password "$SVN_PASSWORD"
    
    print_success "Published $repo_name version $version"
    
    cd "$PLUGIN_DIR"
}

# Main function
main() {
    local version=""
    local dry_run="false"
    local update_ver="false"
    
    # Parse arguments
    for arg in "$@"; do
        case $arg in
            --dry-run)
                dry_run="true"
                ;;
            --update-version)
                update_ver="true"
                ;;
            *)
                if [[ $arg =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
                    version=$arg
                fi
                ;;
        esac
    done
    
    # Get version
    if [ -z "$version" ]; then
        version=$(get_current_version)
        print_status "Using current version: $version"
    else
        validate_version "$version"
        if [ "$update_ver" == "true" ] || [ "$version" != "$(get_current_version)" ]; then
            update_version "$version"
        fi
    fi
    
    echo ""
    echo "=========================================="
    echo "  WordPress.org SVN Publisher"
    echo "  Version: $version"
    if [ "$dry_run" == "true" ]; then
        echo "  Mode: DRY RUN"
    fi
    echo "=========================================="
    echo ""
    
    # Publish to each configured SVN repo
    local success_count=0
    local total_count=0
    
    for repo_name in "${!SVN_REPOS[@]}"; do
        total_count=$((total_count + 1))
        local svn_dir="${SVN_REPOS[$repo_name]}"
        local main_file="${MAIN_FILES[$repo_name]}"
        local plugin_name="${PLUGIN_NAMES[$repo_name]}"
        
        if publish_to_svn "$repo_name" "$svn_dir" "$main_file" "$version" "$dry_run" "$plugin_name"; then
            success_count=$((success_count + 1))
        fi
        echo ""
    done
    
    echo "=========================================="
    print_success "Published $success_count/$total_count repositories"
    echo "=========================================="
}

# Run main function
main "$@"
