<?php

/**
 * Returns a safe image URL from an attachment ID and image size.
 *
 * @param int|string|null $id   The attachment ID from ACF.
 * @param string          $size Image size ('thumbnail', 'medium', 'full', etc.)
 * @return string               Escaped image URL or empty string.
 */
function sacf_image_url($id, $size = 'full'): string {
    if (!$id || !is_numeric($id)) {
        return ''; // Graceful fallback
    }

    $url = wp_get_attachment_image_url((int) $id, $size);

    return $url ? esc_url($url) : '';
}
