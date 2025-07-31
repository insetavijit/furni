DO YOU THINK THIS IS PROFESSIONAL <?php
/**
 * ========================================
 * ACF Theme Option Loader and Debug Utility
 * ========================================
 * This block:
 * - Loads specific ACF option fields (e.g. header data).
 * - Extracts image URLs from image ID fields (cart-logo, profile-logo).
 * - Prints debug info for admins if WP_DEBUG and PAGE_DEBUG are both true.
 */

/** 
 * Define which ACF keys (option fields) you want to load globally.
 * You can add more keys like 'footer-settings', 'global-alerts', etc.
 */
$acf_keys = [
    'header-nav-opt',
];

/**
 * Container to hold the fetched ACF option fields.
 * Each key will be a top-level ACF options group.
 */
$ACF_FIELDS_FRNY = [];

// Loop through the desired ACF keys and retrieve each one from the options page
foreach ($acf_keys as $key) {
    $ACF_FIELDS_FRNY[$key] = get_field($key, 'option');
}

/**
 * Extract specific image URLs from nested ACF options using helper `sacf_image_url()`.
 * This assumes ACF field values under 'header-nav-opt' include image IDs.
 */
$thm_Content = [
    'cart-logo'    => sacf_image_url($ACF_FIELDS_FRNY['header-nav-opt']['cart-logo'] ?? null, 'full'),
    'profile-logo' => sacf_image_url($ACF_FIELDS_FRNY['header-nav-opt']['profile-logo'] ?? null, 'full'),
];

/**
 * Debug flag: Set to true to enable local admin debug output.
 * Use this only in development. Keep it false in production.
 * Admin Debug Panel (Visible only to admins when WP_DEBUG and PAGE_DEBUG are enabled)
 * Useful during theme development or when verifying ACF data structure.
 */
const PAGE_DEBUG = false;
if (current_user_can('manage_options') && defined('WP_DEBUG') && WP_DEBUG && PAGE_DEBUG) {
    echo '<pre style="background:#111; color:#0f0; padding:10px;">';
    #>>>>>----------=>


    print_r($ACF_FIELDS_FRNY);
    print_r($thm_Content);


    #>>>>>-----------=>
    echo '</pre>';
}
?>


<nav class="custom-navbar navbar navbar-expand-md navbar-dark bg-dark" aria-label="Furni navigation bar">
    <div class="container">
        <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>">Furni<span>.</span></a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsFurni"
            aria-controls="navbarsFurni" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarsFurni">
            <?php
            // WordPress Primary Menu Integration

            wp_nav_menu([
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'custom-navbar-nav navbar-nav ms-auto mb-2 mb-md-0',
                'fallback_cb'    => false,
                'depth'          => 2,
                'walker'         => new WP_Bootstrap_Navwalker(),
            ]);

            ?>

            <ul class="custom-navbar-cta navbar-nav mb-2 mb-md-0 ms-5">
                <li><a class="nav-link" href="#"><img src="<?php echo $thm_Content['cart-logo']?>"></a></li>
                <li><a class="nav-link" href="#"><img src="<?php echo $thm_Content['profile-logo']?>"></a></li>
            </ul>
        </div>
    </div>
</nav>
<!-- End Header/Navigation -->
