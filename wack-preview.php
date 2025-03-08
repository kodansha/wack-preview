<?php

/**
 * Plugin Name: WACK Preview
 * Plugin URI: https://github.com/kodansha/wack-preview
 * Description: Helpers to make it possible to preview posts on frontend.
 * Version: 0.0.1
 * Author: KODANSHAtech LLC.
 * Author URI: https://github.com/kodansha
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

// Don't do anything if called directly.
if (!defined('ABSPATH') || !defined('WPINC')) {
    die();
}

// Autoloader
if (is_readable(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Initialize plugin
 */
function wack_preview_init()
{
    (new WackPreview\AdminMenu())->init();
    (new WackPreview\LinkRewriteHook())->init();
}

add_action('plugins_loaded', 'wack_preview_init', PHP_INT_MAX - 1);
