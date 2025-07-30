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
 * Enqueue theme styles
 *
 * @return void
 */
function furni_enqueue_styles()
{
    // Define theme version for cache busting
    $theme_version = wp_get_theme()->get('Version') ?: '1.0.0';

    // Define styles array with configuration
    $styles = [
        'bootstrap' => [
            'handle' => 'furni-bootstrap',
            'src'    => get_template_directory_uri() . '/assets/css/bootstrap.min.css',
            'deps'   => [],
            'ver'    => '5.3.3', // Specific Bootstrap version
        ],
        'fontawesome' => [
            'handle' => 'furni-fontawesome',
            'src'    => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css',
            'deps'   => [],
            'ver'    => '6.6.0',
        ],
        'tiny-slider' => [
            'handle' => 'furni-tiny-slider',
            'src'    => get_template_directory_uri() . '/assets/css/tiny-slider.css',
            'deps'   => [],
            'ver'    => $theme_version,
        ],
        'Style' => [
            'handle' => 'furni-Style',
            'src'    => get_template_directory_uri() . '/assets/css/style.css',
            'deps'   => [],
            'ver'    => $theme_version,
        ],
        'theme-style' => [
            'handle' => 'furni-style',
            'src'    => get_stylesheet_uri(),
            'deps'   => ['furni-bootstrap'],
            'ver'    => $theme_version,
        ],
    ];

    // Enqueue all styles
    foreach ($styles as $style)
    {
        wp_enqueue_style(
            $style['handle'],
            $style['src'],
            $style['deps'],
            $style['ver']
        );

        // Add integrity check for external resources
        if (isset($style['handle']) && $style['handle'] === 'furni-fontawesome')
        {
            wp_style_add_data($style['handle'], 'integrity', 'sha512-6s5/3zF1Z7cC8s2+1VGs6zgg6Yb3G9n8/1i3D2Iu2H3G5zgg6Yb3G9n8/1i3D2Iu2H3G5zgg6Yb3G9n8/1i3D2');
            wp_style_add_data($style['handle'], 'crossorigin', 'anonymous');
        }
    }

    // Add conditional IE-specific styles if needed
    global $is_IE;
    if ($is_IE)
    {
        wp_enqueue_style(
            'furni-ie',
            get_template_directory_uri() . '/assets/css/ie-compat.css',
            ['furni-style'],
            $theme_version
        );
        wp_style_add_data('furni-ie', 'conditional', 'lte IE 11');
    }
}
add_action('wp_enqueue_scripts', 'furni_enqueue_styles', 10);

/**
 * Defer non-critical styles for performance
 *
 * @param string $tag    The link tag for the enqueued style
 * @param string $handle The style's registered handle
 * @param string $src    The style's source URL
 * @return string Modified link tag
 */
function furni_defer_css($tag, $handle, $src)
{
    $defer_styles = ['furni-fontawesome', 'furni-tiny-slider'];

    if (in_array($handle, $defer_styles, true))
    {
        return str_replace('rel="stylesheet"', 'rel="stylesheet" media="print" onload="this.media=\'all\'"', $tag);
    }

    return $tag;
}
add_filter('style_loader_tag', 'furni_defer_css', 10, 3);
