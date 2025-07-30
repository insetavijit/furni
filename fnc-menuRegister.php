<?php

/**
 * Registers navigation menus for the Furni theme.
 *
 * This function sets up the theme's navigation menus by registering primary and secondary menu locations.
 * It includes error checking to ensure WordPress functions are available and provides filter hooks
 * for extending menu locations. The function is hooked to 'after_setup_theme' to ensure proper
 * initialization during theme setup.
 *
 * @since 1.0.0
 * @return void
 */
function furni_register_menus()
{
    // Check if register_nav_menus function exists
    if (!function_exists('register_nav_menus'))
    {
        // Log error if WordPress menu registration function is unavailable
        if (defined('WP_DEBUG') && WP_DEBUG)
        {
            error_log('Furni Theme: register_nav_menus function is not available');
        }
        return;
    }

    // Define default menu locations
    $default_locations = [
        'primary'   => esc_html__('Primary Menu', 'furni'),
        'secondary' => esc_html__('Secondary Menu', 'furni'),
    ];

    /**
     * Filter the navigation menu locations before registration.
     *
     * Allows developers to modify or add menu locations programmatically.
     *
     * @since 1.0.0
     * @param array $default_locations Array of menu locations with their descriptions.
     */
    $menu_locations = apply_filters('furni_menu_locations', $default_locations);

    // Validate menu locations array
    if (!is_array($menu_locations) || empty($menu_locations))
    {
        if (defined('WP_DEBUG') && WP_DEBUG)
        {
            error_log('Furni Theme: Invalid or empty menu locations array');
        }
        return;
    }

    // Register the navigation menus
    register_nav_menus($menu_locations);

    /**
     * Action hook after menu registration.
     *
     * Allows additional functionality to be added after menus are registered.
     *
     * @since 1.0.0
     * @param array $menu_locations The registered menu locations.
     */
    do_action('furni_after_menu_registration', $menu_locations);
}

// Hook the function to after_setup_theme with a priority of 10
add_action('after_setup_theme', 'furni_register_menus', 10);
