<?php

/**
 * Plugin Name: WACK Preview
 * Plugin URI: https://github.com/kodansha/wack-preview
 * Description: Helpers to make it possible to preview posts on frontend.
 * Version: 0.1.3
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
 * Verify preview token
 *
 * This is a utility function provided as a convenient API.
 * It can be used in WordPress themes to verify preview tokens and control access to unpublished posts.
 *
 * @param string $preview_token The token string (JWT) to verify
 *
 * @return bool Returns true if the token is valid, false otherwise
 *
 * @example
 * if (wack_preview_verify_token($token)) {
 *     // Allow preview access
 * } else {
 *     // Deny preview access
 * }
 */
function wack_preview_verify_token(string $preview_token): bool
{
    $token_manager = new WackPreview\TokenManager();
    return $token_manager->verifyToken($preview_token);
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
