<?php

/**
 * Plugin Name: Super Simple Social Share
 * Plugin URI: https://github.com/mattruetz/super-simple-social-share
 * Description: A simple social sharing plugin that adds social media icons with tooltips using FontAwesome icons.
 * Version: 1.0.0
 * Author: Matt Ruetz
 * Author URI: https://mattruetz.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: super-simple-social-share
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('SSSS_VERSION', '1.0.0');
define('SSSS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SSSS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once SSSS_PLUGIN_DIR . 'includes/class-super-simple-social-share.php';
require_once SSSS_PLUGIN_DIR . 'admin/class-super-simple-social-share-admin.php';

// Initialize the plugin
function run_super_simple_social_share()
{
    $plugin = new Super_Simple_Social_Share();
    $plugin->run();
}

// Hook into WordPress
add_action('plugins_loaded', 'run_super_simple_social_share');
