<?php
/**
 * AISEO Core Plugin Class
 *
 * Central initialization and coordination
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Core {
    
    /**
     * Plugin components
     */
    private $api;
    private $rest;
    private $meta;
    private $analysis;
    private $schema;
    private $sitemap;
    private $social;
    private $admin;
    private $metabox;
    private $image_seo;
    private $homepage_seo;
    private $taxonomy_seo;
    private $webmaster;
    private $analytics;
    private $title_templates;
    private $robots;
    private $breadcrumbs;
    private $rss;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Components will be loaded on init
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        $this->load_dependencies();
        $this->init_hooks();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Core components are autoloaded
        // Initialize them here
        $this->api = new AISEO_API();
        $this->rest = new AISEO_REST();
        $this->meta = new AISEO_Meta();
        $this->analysis = new AISEO_Analysis();
        $this->social = new AISEO_Social();
        $this->sitemap = new AISEO_Sitemap();
        
        // Initialize meta tags handler
        $this->meta->init();
        
        // Initialize social media tags handler
        $this->social->init();
        
        // Initialize sitemap generator
        $this->sitemap->init();
        
        // Initialize homepage SEO
        $this->homepage_seo = new AISEO_Homepage_SEO();
        $this->homepage_seo->init();
        
        // Initialize taxonomy SEO
        $this->taxonomy_seo = new AISEO_Taxonomy_SEO();
        $this->taxonomy_seo->init();
        
        // Initialize webmaster verification
        $this->webmaster = new AISEO_Webmaster();
        $this->webmaster->init();
        
        // Initialize analytics
        $this->analytics = new AISEO_Analytics();
        $this->analytics->init();
        
        // Initialize title templates
        $this->title_templates = new AISEO_Title_Templates();
        $this->title_templates->init();
        
        // Initialize robots settings
        $this->robots = new AISEO_Robots();
        $this->robots->init();
        
        // Initialize breadcrumbs
        $this->breadcrumbs = new AISEO_Breadcrumbs();
        $this->breadcrumbs->init();
        
        // Initialize RSS customization
        $this->rss = new AISEO_RSS();
        $this->rss->init();
        
        // Initialize metabox and image SEO (only in admin)
        if (is_admin()) {
            $this->metabox = new AISEO_Metabox();
            $this->metabox->init();
            
            $this->image_seo = new AISEO_Image_SEO();
        }
        
        // Other components will be initialized as needed
    }
    
    /**
     * Register WordPress hooks
     */
    private function init_hooks() {
        // Schedule cron jobs
        add_action('init', array($this, 'schedule_cron_jobs'));
        
        // Add custom cron intervals
        add_filter('cron_schedules', array($this, 'add_cron_intervals'));
        
        // Register REST API routes
        add_action('rest_api_init', array($this->rest, 'register_routes'));
    }
    
    /**
     * Define admin-specific hooks
     */
    private function define_admin_hooks() {
        if (!is_admin()) {
            return;
        }
        
        // Admin hooks will be added here
    }
    
    /**
     * Define public-facing hooks
     */
    private function define_public_hooks() {
        // Public hooks will be added here
    }
    
    /**
     * Schedule cron jobs
     */
    public function schedule_cron_jobs() {
        // Cache warming - daily at 3 AM
        if (!wp_next_scheduled('aiseo_cache_warming')) {
            wp_schedule_event(strtotime('tomorrow 03:00:00'), 'daily', 'aiseo_cache_warming');
        }
        
        // Process request queue - every 5 minutes
        if (!wp_next_scheduled('aiseo_process_queue')) {
            wp_schedule_event(time(), 'aiseo_five_minutes', 'aiseo_process_queue');
        }
        
        // Cleanup old logs - weekly
        if (!wp_next_scheduled('aiseo_cleanup_logs')) {
            wp_schedule_event(time(), 'weekly', 'aiseo_cleanup_logs');
        }
    }
    
    /**
     * Add custom cron intervals
     *
     * @param array $schedules Existing schedules
     * @return array Modified schedules
     */
    public function add_cron_intervals($schedules) {
        $schedules['aiseo_five_minutes'] = array(
            'interval' => 300,
            'display' => __('Every 5 Minutes', 'aiseo'),
        );
        
        return $schedules;
    }
    
    /**
     * Get API instance
     *
     * @return AISEO_API
     */
    public function get_api() {
        return $this->api;
    }
}
