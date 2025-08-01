<?php

/**
 * Header Navigation
 * Handles cart and profile logo retrieval from ACF options
 */


$acfClass = [
    'Name' => 'header-hero',
    'Location' => 'option',
    'ONPAGE_DEBUG' => TRUE
];

$thm_options = get_field($acfClass['Name'], $acfClass['Location']);
// Validate that we have the necessary data structure
if (!$thm_options || !isset($thm_options['header-hero']))
{
    error_log('Header Navigation: Missing theme options or header-nav-opt configuration');
}

$PARRENT = $thm_options;
$acf_content = [
    $acfClass['Name'] => $PARRENT,
    'headline' => $PARRENT['headline'],
    'details' => $PARRENT['details'],
    'explore_page_url' => $PARRENT['explore_page_url'],
    'payment_page' => $PARRENT['payment_page'],
    'fetaure_product_image' => $PARRENT['fetaure_product_image'],
];

// Debug output for administrators (only in debug mode)
if (current_user_can('manage_options') && defined('WP_DEBUG') && WP_DEBUG && $acfClass['ONPAGE_DEBUG'])
{
    echo '<div class="debug-info" style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-left: 4px solid #0073aa;">';
    echo '<h4>Header Navigation Debug Info:</h4>';
    echo '<pre style="background: white; padding: 10px; overflow-x: auto;">';

    print_r($acf_content);
    echo '</pre>';
    echo '</div>';
}
?>
<div class="hero">
    <div class="container">
        <div class="row justify-content-between">
            <div class="col-lg-5">
                <div class="intro-excerpt">
                    <h1><?php echo $acf_content['headline']; ?></h1>
                    <p class="mb-4"><?php echo $acf_content['details']; ?></p>
                    <p>
                        <a
                            href="<?php echo $acf_content['payment_page']; ?>"
                            class="btn btn-secondary me-2">Buy Now</a>
                        <a
                            href="<?php echo $acf_content['explore_page_url']; ?>"
                            class="btn btn-white-outline">Explore</a>
                    </p>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="hero-img-wrap">
                    <img
                        src=<?php echo $acf_content['fetaure_product_image'] ?>
                        class="img-fluid">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Hero Section -->