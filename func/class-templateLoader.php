<?php

/**
 * PageLoader Class
 *
 * Dynamically loads template parts with caching, security, and extensibility.
 * Designed for WordPress themes with enhanced error handling and debugging support.
 *
 * @package Your_Theme_Name
 * @since 1.0.0
 */

namespace furni\Loader;

if (!class_exists(__NAMESPACE__ . '\\PageLoader')) :

    /**
     * PageLoader class for loading template parts.
     */
    final class PageLoader
    {
        /**
         * Singleton instance.
         *
         * @var PageLoader|null
         */
        private static ?PageLoader $instance = null;

        /**
         * Array of template relative paths.
         *
         * @var string[]
         */
        protected array $templates = [];

        /**
         * Configuration constants
         */
        private const CACHE_GROUP = 'furni_page_loader';
        private const CACHE_DURATION = HOUR_IN_SECONDS;
        private const ALLOWED_EXTENSIONS = ['.php'];
        private const MAX_PATH_LENGTH = 255;

        /**
         * Private constructor to enforce singleton pattern.
         *
         * @param string[] $templates Array of relative paths to template parts.
         * @throws \InvalidArgumentException If templates array contains invalid entries.
         */
        private function __construct(array $templates)
        {
            try
            {
                $this->templates = $this->filter_templates($templates);
            }
            catch (\InvalidArgumentException $e)
            {
                $this->log_error('Constructor error: ' . $e->getMessage());
                throw $e;
            }
        }

        /**
         * Get or create the singleton instance.
         *
         * @param string[] $templates Array of relative paths to template parts.
         * @return PageLoader
         * @throws \InvalidArgumentException If templates array contains invalid entries.
         */
        public static function get_instance(array $templates = []): self
        {
            try
            {
                if (null === self::$instance)
                {
                    self::$instance = new self($templates);
                }
                elseif (!empty($templates))
                {
                    self::$instance->templates = self::$instance->filter_templates($templates);
                }
                return self::$instance;
            }
            catch (\Exception $e)
            {
                // Debug hint: Check if templates array contains valid paths
                self::log_error('Failed to get instance: ' . $e->getMessage());
                throw new \RuntimeException('Failed to initialize PageLoader: ' . $e->getMessage());
            }
        }

        /**
         * Filter and validate template paths.
         *
         * @param string[] $templates Array of template paths.
         * @return string[] Filtered template paths.
         * @throws \InvalidArgumentException If template paths are invalid.
         */
        private function filter_templates(array $templates): array
        {
            try
            {
                $templates = apply_filters('furni_page_loader_templates', $templates);

                return array_filter($templates, function ($template)
                {
                    // Validate template is a non-empty string
                    if (!is_string($template) || empty(trim($template)))
                    {
                        $this->log_error("Invalid template type or empty: " . print_r($template, true));
                        return false;
                    }

                    // Validate file extension
                    $ext = pathinfo($template, PATHINFO_EXTENSION);
                    if (!in_array('.' . $ext, self::ALLOWED_EXTENSIONS, true))
                    {
                        $this->log_error("Invalid template extension for: {$template}");
                        return false;
                    }

                    // Validate path length
                    if (strlen($template) > self::MAX_PATH_LENGTH)
                    {
                        $this->log_error("Template path too long: {$template}");
                        return false;
                    }

                    return true;
                });
            }
            catch (\Exception $e)
            {
                $this->log_error('Template filtering failed: ' . $e->getMessage());
                throw new \InvalidArgumentException('Invalid template paths provided');
            }
        }

        /**
         * Load template parts on the appropriate WordPress hook.
         *
         * @return void
         */
        public static function init(): void
        {
            try
            {
                add_action('template_redirect', [self::get_instance([]), 'load_templates']);
            }
            catch (\Exception $e)
            {
                // Debug hint: Check if template_redirect hook is properly registered
                self::log_error('Initialization failed: ' . $e->getMessage());
            }
        }

        /**
         * Load template parts.
         *
         * @return void
         */
        public function load_templates(): void
        {
            // echo date("Y-m-d H:i:s", time());

            try
            {
                if (empty($this->templates))
                {
                    $this->log_warning('[ error in input >- template part ] provided to PageLoader.');
                    return;
                }

                $cache_key = md5(serialize($this->templates));
                $cached_paths = wp_cache_get($cache_key, self::CACHE_GROUP);
                $template_paths = is_array($cached_paths) ? $cached_paths : [];

                if (empty($template_paths))
                {
                    foreach ($this->templates as $template)
                    {
                        try
                        {
                            $sanitized = $this->sanitize_path($template);
                            $path = $this->resolve_path($sanitized);

                            if ($path && file_exists($path))
                            {
                                $template_paths[$sanitized] = $path;
                            }
                            else
                            {
                                $this->log_error(sprintf('Missing template: %s', $path ?: $sanitized));
                            }
                        }
                        catch (\Exception $e)
                        {
                            $this->log_error("Failed to process template {$template}: " . $e->getMessage());
                            continue;
                        }
                    }
                    // Cache paths for performance
                    wp_cache_set($cache_key, $template_paths, self::CACHE_GROUP, self::CACHE_DURATION);
                }

                foreach ($template_paths as $template => $path)
                {
                    
                    try
                    {
                        // Debug hint: Use furni_before_template_part hook to modify template loading
                        do_action('furni_before_template_part', $template, $path);

                        require_once $path;

                        // Debug hint: Use furni_after_template_part hook to track loaded templates
                        do_action('furni_after_template_part', $template, $path);
                    }
                    catch (\Exception $e)
                    {
                        $this->log_error("Failed to load template {$template}: " . $e->getMessage());
                        continue;
                    }
                }
            }
            catch (\Exception $e)
            {
                // Debug hint: Check template paths and file permissions
                $this->log_error('Template loading failed: ' . $e->getMessage());
            }
        }

        /**
         * Sanitize template path to prevent directory traversal.
         *
         * @param string $template Relative template path.
         * @return string Sanitized path.
         */
        private function sanitize_path(string $template): string
        {
            // Remove dangerous characters and normalize path
            $sanitized = preg_replace('/[^a-zA-Z0-9\/\-\_\.]/', '', $template);
            $sanitized = ltrim(str_replace(['../', '..\\'], '', $sanitized), '/');
            return $sanitized;
        }

        /**
         * Resolve the full filesystem path securely, supporting child themes.
         *
         * @param string $template Relative template path.
         * @return string|null Full path or null if invalid.
         */
        protected function resolve_path(string $template): ?string
        {
            try
            {
                // Use locate_template to support child themes
                $path = locate_template($template, false, false);

                if (!$path)
                {
                    $base_dir = get_template_directory();
                    $path = trailingslashit($base_dir) . $template;

                    // Validate path is within theme directory
                    $real_path = realpath($path) ?: '';
                    $real_base = realpath($base_dir) ?: '';
                    if (empty($real_path) || 0 !== strpos($real_path, $real_base))
                    {
                        $this->log_error("Invalid template path detected: {$path}");
                        return null;
                    }
                }

                // Additional security check
                if (!is_readable($path))
                {
                    $this->log_error("Template file not readable: {$path}");
                    return null;
                }

                return $path;
            }
            catch (\Exception $e)
            {
                // Debug hint: Verify theme directory permissions and path structure
                $this->log_error("Path resolution failed for {$template}: " . $e->getMessage());
                return null;
            }
        }

        /**
         * Clear cached template paths on theme switch.
         *
         * @return void
         */
        public static function clear_cache(): void
        {
            try
            {
                if (self::$instance)
                {
                    wp_cache_delete(md5(serialize(self::$instance->templates)), self::CACHE_GROUP);
                }
            }
            catch (\Exception $e)
            {
                // Debug hint: Check cache group and permissions
                self::log_error('Cache clearing failed: ' . $e->getMessage());
            }
        }

        /**
         * Log error messages when WP_DEBUG is enabled.
         *
         * @param string $message Error message to log.
         * @return void
         */
        private function log_error(string $message): void
        {
            if (defined('WP_DEBUG') && WP_DEBUG)
            {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log(sprintf('[PageLoader Error] %s', $message));
            }
        }

        /**
         * Log warning messages when WP_DEBUG is enabled.
         *
         * @param string $message Warning message to log.
         * @return void
         */
        private function log_warning(string $message): void
        {
            if (defined('WP_DEBUG') && WP_DEBUG)
            {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
                trigger_error(
                    esc_html__($message, 'furni'),
                    E_USER_WARNING
                );
            }
        }
    }

    // Initialize the loader
    try
    {
        add_action('after_switch_theme', [PageLoader::class, 'clear_cache']);
    }
    catch (\Exception $e)
    {
        // Debug hint: Check WordPress hook registration
        error_log('PageLoader initialization failed: ' . $e->getMessage());
    }

endif;
