<?php

/**
 * Header Navigation
 * Handles cart and profile logo retrieval from ACF options
 */

const ONPAGE_DEBUG  = false ;
$thm_options = furni_get_acf_content();

// Validate that we have the necessary data structure
if (!$thm_options || !isset($thm_options['header-nav-opt']))
{
    error_log('Header Navigation: Missing theme options or header-nav-opt configuration');
    return;
}

$header_nav_options = $thm_options['header-nav-opt'];
$acf_content = [
    'header_nav_options' => $header_nav_options,
    'cart-logo' => esc_url(wp_get_attachment_image_url($header_nav_options['cart-logo'], 'full')),
    'profile-logo' => esc_url(wp_get_attachment_image_url($header_nav_options['profile-logo'], 'full'))
];

// Debug output for administrators (only in debug mode)
if (current_user_can('manage_options') && defined('WP_DEBUG') && WP_DEBUG && ONPAGE_DEBUG)
{
    echo '<div class="debug-info" style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-left: 4px solid #0073aa;">';
    echo '<h4>Header Navigation Debug Info:</h4>';
    echo '<pre style="background: white; padding: 10px; overflow-x: auto;">';

    print_r($acf_content);
    echo '</pre>';
    echo '</div>';
}
?>
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
                        <img src="<?php echo $acf_content['cart-logo'] ?>">
                    </a>
                </li>
                <li>
                    <a class="nav-link" href="http://localhost/wp-devl/ipsum/wp-admin/">
                        <img src="<?php echo $acf_content['profile-logo'] ?>">
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- End Header Navigation -->