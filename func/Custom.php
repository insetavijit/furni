<?PHP
/**
 * TODAY : 31JULY2025 ~ avijitSarkar :
 * https://github.com/insetavijit/furni
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