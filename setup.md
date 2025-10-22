# Syncing GitHub to SVN: The Integrated `git-svn` Workflow

This guide covers the one-time setup to link your Git repository with the WordPress SVN and the recurring workflow for deploying updates.

---

### Part 1: One-Time Setup

Follow these steps on a fresh clone of your GitHub repository to establish the link with the WordPress SVN.

**1. Create the `svnsync` Branch**

This branch will be used to communicate with the SVN repository.

```bash
git branch --no-track svnsync
```

**2. Configure the SVN Remote**

These commands tell Git where the SVN repository is located and how its branches and tags are structured.

```bash
git config --add svn-remote.svn.url https://plugins.svn.wordpress.org/seo-wordpress
git config --add svn-remote.svn.fetch 'trunk:refs/remotes/origin/trunk'
git config --add svn-remote.svn.tags 'tags/*:refs/remotes/origin/tags/*'
git config --add svn-remote.svn.branches 'branches/*:refs/remotes/origin/branches/*'
```

**3. Perform the Initial SVN Fetch**

This command downloads the entire history from the SVN repository. **This will take a very long time.**

```bash
git checkout svnsync
git svn fetch
```

Once this is complete, your repository is fully configured. Proceed to Part 2 for the deployment workflow.

---

### Part 2: Deploying an Update (Recurring Workflow)

This guide details how to push updates from your local Git repository directly to the WordPress.org SVN repository. This process uses the `svnsync` branch which is linked to the SVN `trunk`.

**Replace `X.X.X` with the new version number in the commands below.**

### Step 1: Ensure Your `master` Branch is Up-to-Date

Make sure your latest changes are committed and pushed to GitHub.

```bash
git checkout master
git pull origin master
```

### Step 2: Synchronize with the SVN `trunk`

Switch to the `svnsync` branch and pull the latest changes from the SVN `trunk`. This ensures you don't overwrite any other changes.

```bash
git checkout svnsync
git svn rebase
```

### Step 3: Merge Your Changes

Merge the changes from your `master` branch into the `svnsync` branch.

```bash
git merge master
```

### Step 4: Commit to the SVN `trunk`

This command pushes your merged changes from the `svnsync` branch to the SVN `trunk`.

```bash
git svn dcommit --username USERNAME
```

### Step 5: Tag the New Version in SVN

This command creates the new version tag from the SVN `trunk`, releasing the update to users.

```bash
git svn tag X.X.X -m "Tagging version X.X.X" --username USERNAME
```

### Step 6: Update Your `master` Branch (Optional)

It's good practice to merge the `svnsync` branch back into `master` to keep them aligned.

```bash
git checkout master
git merge svnsync
git push origin master
```
