<?php

/**
 * Template Name: Furni Theme Base
 * Author: Untree.co
 * Template URI: https://untree.co/
 * License: https://creativecommons.org/licenses/by/3.0/
 */

use furni\Loader\PageLoader;
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="author" content="Untree.co">
    <meta name="description" content="">
    <meta name="keywords" content="bootstrap, bootstrap5, furniture, interior design">

    <link rel="shortcut icon" href="<?php echo esc_url(get_template_directory_uri()); ?>/favicon.png">

    <title><?php wp_title('|', true, 'right'); ?><?php bloginfo('name'); ?></title>

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

    <?php
    PageLoader::get_instance([
        '/template-parts/index-navBar.php',
        // '/template-parts/index-hero.php',
        // '/template-parts/index-productSelection.php',
        // '/template-parts/index-whyChoseus.php',
        // '/template-parts/index-weHelpSection.php',
        // '/template-parts/index-testimonialScetion.php',
        // '/template-parts/index-blogSec.php',
        // '/template-parts/index-footer.php',
    ])->load_templates();
    ?>

    <?php wp_footer(); ?>
</body>

</html>