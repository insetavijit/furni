<?php

/**
 * Theme Function Loader
 *
 * Dynamically loads custom function and class files from the `/inc` directory.
 * Provides secure, performant, and extensible file inclusion for WordPress themes.
 *
 * @package furniName
 * @since 1.0.0
 */

namespace furniTheme\fnc_loader;

use WP_Error;

if (! defined('ABSPATH'))
{
    exit; // Prevent direct access.
}

/**
 * Theme Function Loader Class
 *
 * Singleton class to handle dynamic inclusion of theme files with security and performance optimizations.
 */
class Theme_Function_Loader
{
    /**
     * Singleton instance.
     *
     * @var Theme_Function_Loader|null
     */
    private static $instance = null;

    /**
     * Directory for include files.
     *
     * @var string
     */
    private $inc_dir;

    /**
     * Cache key for file existence.
     *
     * @var string
     */
    private $cache_key = 'furni_fnc_loader_cache';

    /**
     * List of included files to prevent duplicates.
     *
     * @var array
     */
    private $included_files = [];

    /**
     * Private constructor for singleton pattern.
     */
    private function __construct()
    {
        $this->inc_dir = trailingslashit(get_template_directory() . '/func');
    }

    /**
     * Get singleton instance.
     *
     * @return Theme_Function_Loader
     */
    public static function get_instance(): self
    {
        if (null === self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the loader and hook into WordPress.
     *
     * @return void
     */
    public static function init(): void
    {
        $instance = self::get_instance();
        add_action('after_setup_theme', [$instance, 'load_fnc_loader'], 0);
    }

    /**
     * Load theme include files.
     *
     * @return void
     */
    public function load_fnc_loader(): void
    {
        // Define default include files.
        $default_files = [
            'fnc-scriptenQue',
            'fnc-styleenQue',
            'class-templateLoader',
            'class-wp-bootstrap-navwalker',
            // 'fnc-MenuRegistration',
            'Custom'
        ];

        /**
         * Filter the list of theme include files.
         *
         * @param array $files Array of base filenames (without .php).
         */
        $theme_function_files = apply_filters('furni_include_files', $default_files);

        // Get cached file existence results, if available.
        $file_cache = wp_cache_get($this->cache_key, 'furni');
        if (false === $file_cache)
        {
            $file_cache = [];
        }

        foreach ($theme_function_files as $filename)
        {
            // Sanitize filename to prevent directory traversal.
            $sanitized_filename = sanitize_file_name($filename . '.php');
            $filepath = $this->inc_dir . $sanitized_filename;

            // Validate file path to ensure it's within the /inc directory.
            $real_filepath = realpath($filepath);
            if (false === $real_filepath || strpos($real_filepath, realpath($this->inc_dir)) !== 0)
            {
                $this->log_error('Invalid file path detected', ['filepath' => $filepath]);
                continue;
            }

            // Check file existence, using cache if available.
            if (! isset($file_cache[$sanitized_filename]))
            {
                $file_cache[$sanitized_filename] = file_exists($filepath);
                wp_cache_set($this->cache_key, $file_cache, 'furni', HOUR_IN_SECONDS);
            }

            // Include file if it exists and hasn't been included.
            if ($file_cache[$sanitized_filename] && ! in_array($filepath, $this->included_files, true))
            {
                require_once $filepath;
                $this->included_files[] = $filepath;
            }
            elseif (! $file_cache[$sanitized_filename])
            {
                $this->log_error('Missing include file', ['filepath' => $filepath]);
            }
        }
    }

    /**
     * Log errors to WordPress debug log or WP_Error.
     *
     * @param string $message Error message.
     * @param array  $context Additional context for the error.
     * @return void
     */
    private function log_error(string $message, array $context = []): void
    {
        $error_message = sprintf('[furniName Error] %s: %s', $message, wp_json_encode($context));

        if (defined('WP_DEBUG') && WP_DEBUG)
        {
            error_log($error_message);
        }

        // Optionally store errors for admin display or further processing.
        if (is_admin())
        {
            $error = new WP_Error('furni_loader_error', $message, $context);
            set_transient('furni_loader_errors', $error, HOUR_IN_SECONDS);
        }
    }
}

// Initialize the loader.
Theme_Function_Loader::init();
