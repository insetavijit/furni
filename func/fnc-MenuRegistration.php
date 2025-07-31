<?php
/**
 * Register Navigation Menus for the Furni Theme
 *
 * Registers WordPress menu locations for the Furni theme with support for extensibility
 * and basic error handling. Designed for simplicity and performance in WordPress theme development.
 *
 * @package Your_Theme_Name
 * @since 1.0.0
 */

namespace furni\Menu;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register navigation menus for the Furni theme.
 *
 * Registers primary and secondary menu locations, with support for additional locations
 * via the 'furni_menu_locations' filter. Includes basic validation and error logging.
 *
 * @since 1.0.0
 * @return void
 */
function register_theme_menus(): void {
    // Define default menu locations
    $menu_locations = [
        'primary'   => __('Primary Menu', 'furni'),   // Header or main navigation
        'secondary' => __('Secondary Menu', 'furni'), // Footer or sidebar navigation
    ];

    // Allow customization of menu locations via filter
    $menu_locations = apply_filters('furni_menu_locations', $menu_locations);

    // Basic validation to ensure valid menu locations
    if (!is_array($menu_locations) || empty($menu_locations)) {
        log_error('No valid menu locations provided after filtering.');
        return;
    }

    // Sanitize menu locations
    $sanitized_locations = [];
    foreach ($menu_locations as $location => $description) {
        if (is_string($location) && is_string($description) && !empty(trim($location)) && !empty(trim($description))) {
            $sanitized_locations[sanitize_key($location)] = esc_html__($description, 'furni');
        } else {
            log_error("Invalid menu location or description: {$location}");
        }
    }

    // Register menus if valid locations exist
    if (!empty($sanitized_locations)) {
        register_nav_menus($sanitized_locations);
    } else {
        log_error('No valid menu locations after sanitization.');
    }
}

/**
 * Log error messages when WP_DEBUG is enabled.
 *
 * @since 1.0.0
 * @param string $message Error message to log.
 * @return void
 */
function log_error(string $message): void {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log(sprintf('[Furni Menu Error] %s', $message));
    }
}

// Register the menus on the after_setup_theme hook
add_action('after_setup_theme', __NAMESPACE__ . '\\register_theme_menus');