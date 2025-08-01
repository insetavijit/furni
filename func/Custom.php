<?php
/**
 * Simple ACF Content Fetcher
 * Gets specific ACF fields from the options page.
 * 
 * @package Furni
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

/**
 * Returns specific ACF option fields.
 *
 * @return array<string, mixed>
 */
function furni_get_acf_content(): array {
    if (!function_exists('get_field')) {
        error_log('ACF not available.');
        return [];
    }

    return [
        'header-nav-opt' => get_field('header-nav-opt', 'option') ?: [],
    ];
}
