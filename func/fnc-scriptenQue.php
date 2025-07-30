<?php

/**
 * Theme Asset Enqueue Functions
 *
 * Handles the registration and enqueuing of theme styles and scripts
 *
 * @package furni_Theme
 * @since 1.0.0
 */


/**
 * Enqueue theme scripts
 *
 * @return void
 */
function furni_enqueue_scripts()
{
    // Define theme version for cache busting
    $theme_version = wp_get_theme()->get('Version') ?: '1.0.0';

    // Define scripts array with configuration
    $scripts = [
        'bootstrap' => [
            'handle' => 'furni-bootstrap',
            'src'    => get_template_directory_uri() . '/assets/js/bootstrap.bundle.min.js',
            'deps'   => ['jquery'], // Add jQuery dependency
            'ver'    => '5.3.3',
            'footer' => true,
        ],
        'furni-tiny-slider' => [
            'handle' => 'furni-tiny-slider',
            'src'    => get_template_directory_uri() . '/assets/js/tiny-slider.js',
            'deps'   => ['jquery', 'furni-bootstrap'],
            'ver'    => $theme_version,
            'footer' => true,
        ],
        'theme-script' => [ // custom.js
            'handle' => 'furni-custom',
            'src'    => get_template_directory_uri() . '/assets/js/custom.js',
            'deps'   => ['jquery', 'furni-bootstrap'],
            'ver'    => $theme_version,
            'footer' => true,
        ],
    ];

    // Enqueue all scripts
    foreach ($scripts as $script)
    {
        wp_enqueue_script(
            $script['handle'],
            $script['src'],
            $script['deps'],
            $script['ver'],
            $script['footer']
        );
    }

    // Localize script for AJAX or other dynamic data
    wp_localize_script(
        'furni-main',
        'furni_vars',
        [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('furni_nonce'),
            'theme_url' => get_template_directory_uri(),
        ]
    );

    // Add inline script for critical JavaScript if needed
    wp_add_inline_script(
        'furni-main',
        'const furni = furni || {}; furni.init = function() { /* Add initialization code here */ };'
    );

    // Enable threaded comments if applicable
    if (is_singular() && comments_open() && get_option('thread_comments'))
    {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'furni_enqueue_scripts', 10);
/**
 * Clean up script tags for better performance
 *
 * @param string $tag    The script tag for the enqueued script
 * @param string $handle The script's registered handle
 * @param string $src    The script's source URL
 * @return string Modified script tag
 */
function furni_clean_script_tag($tag, $handle, $src)
{
    // Add async/defer attributes to specific scripts
    $async_scripts = ['furni-bootstrap'];

    if (in_array($handle, $async_scripts, true))
    {
        return str_replace('<script ', '<script async ', $tag);
    }

    return $tag;
}
add_filter('script_loader_tag', 'furni_clean_script_tag', 10, 3);
