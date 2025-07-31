<?php

/**
 * ACF Theme Option Loader and Navigation Component
 *
 * Loads ACF option fields, extracts image URLs, and renders a Bootstrap-based navigation bar.
 * Includes robust error handling, secure output, and admin-only debugging.
 *
 * @package Furni
 * @since 1.0.0
 */

/**
 * Helper function to safely retrieve ACF image URLs.
 *
 * @param mixed  $image_field ACF image field (ID, array, or null).
 * @param string $size       Image size (e.g., 'full', 'thumbnail').
 * @param string $fallback   Fallback image URL if field is invalid.
 * @return string Escaped image URL.
 */
function furni_get_acf_image_url($image_field, $size = 'full', $fallback = '')
{
    if (function_exists('wp_get_attachment_image_url') && !empty($image_field))
    {
        if (is_numeric($image_field))
        {
            // Image ID
            $url = wp_get_attachment_image_url($image_field, $size);
            return $url ? esc_url($url) : esc_url($fallback);
        }
        elseif (is_array($image_field) && !empty($image_field['ID']))
        {
            // Image array
            $url = wp_get_attachment_image_url($image_field['ID'], $size);
            return $url ? esc_url($url) : esc_url($fallback);
        }
    }
    return esc_url($fallback);
}

/**
 * Define ACF option fields to load globally.
 * Add more keys as needed (e.g., 'footer-settings', 'global-alerts').
 */
$acf_keys = [
    'header-nav-opt',
];

/**
 * Container for fetched ACF option fields.
 * Each key corresponds to a top-level ACF options group.
 *
 * @var array $ACF_FIELDS_FRNY
 */
$ACF_FIELDS_FRNY = [];

// Check if ACF is active before fetching fields
if (function_exists('get_field'))
{
    foreach ($acf_keys as $key)
    {
        $ACF_FIELDS_FRNY[$key] = get_field($key, 'option');
    }
}
else
{
    error_log('Furni Theme: ACF plugin is not active. Header options will not be loaded.');
}

/**
 * Extract image URLs from ACF fields with fallbacks.
 *
 * @var array $thm_Content
 */
$thm_Content = [
    'cart-logo'    => furni_get_acf_image_url(
        $ACF_FIELDS_FRNY['header-nav-opt']['cart-logo'] ?? null,
        'full'
    ),
    'profile-logo' => furni_get_acf_image_url(
        $ACF_FIELDS_FRNY['header-nav-opt']['profile-logo'] ?? null,
        'full'
    ),
];

/**
 * Debug flag: Enable admin-only debug output in development.
 * Requires WP_DEBUG and PAGE_DEBUG to be true.
 */
const PAGE_DEBUG = false;
if (current_user_can('manage_options') && defined('WP_DEBUG') && WP_DEBUG && PAGE_DEBUG)
{
    echo '<pre style="background:#111;color:#0f0;padding:10px;font-size:14px;">';
    echo "ACF Fields:\n";
    print_r($ACF_FIELDS_FRNY);
    echo "\nTheme Content:\n";
    print_r($thm_Content);
    echo '</pre>';
}
?>

<!-- Header Navigation -->
<nav class="custom-navbar navbar navbar-expand-md navbar-dark bg-dark" aria-label="<?php esc_attr_e('Furni navigation bar', 'furni'); ?>">
    <div class="container">
        <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>">
            <?php esc_html_e('Furni', 'furni'); ?><span>.</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsFurni"
            aria-controls="navbarsFurni" aria-expanded="false" aria-label="<?php esc_attr_e('Toggle navigation', 'furni'); ?>">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarsFurni">
            <?php
            // WordPress Primary Menu Integration
            $nav_args = [
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'custom-navbar-nav navbar-nav ms-auto mb-2 mb-md-0',
                'fallback_cb'    => false,
                'depth'          => 2,
            ];

            if (class_exists('WP_Bootstrap_Navwalker'))
            {
                $nav_args['walker'] = new WP_Bootstrap_Navwalker();
            }
            else
            {
                error_log('Furni Theme: WP_Bootstrap_Navwalker not found. Falling back to default menu rendering.');
            }

            wp_nav_menu($nav_args);
            ?>

            <ul class="custom-navbar-cta navbar-nav mb-2 mb-md-0 ms-5">
                <li>
                    <a class="nav-link" href="#">
                        <img src="<?php echo $thm_Content['cart-logo'] ?>">
                    </a>
                </li>
                <li>
                    <a class="nav-link" href="http://localhost/wp-devl/ipsum/wp-admin/">
                        <img src="<?php echo $thm_Content['profile-logo'] ?>">
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- End Header Navigation -->