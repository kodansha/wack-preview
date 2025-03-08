<?php

namespace WackPreview;

/**
 * Class PluginSettings
 * @package WackPreview
 */
final class PluginSettings
{
    private static PluginSettings $instance;

    private string $frontend_base_url;
    private array $preview_token;
    private array $path_mappings;
    private bool $disable_permalink_rewrite;

    final private function __construct()
    {
        $this->frontend_base_url = self::getFrontendBaseUrl();
        $this->preview_token = self::getPreviewToken();
        $this->path_mappings = self::getPathMappings();
        $this->disable_permalink_rewrite = self::getDisablePermalinkRewriteOption();
    }

    /**
     * Get frontend base URL
     * @return string Frontend base URL
     */
    public function frontendBaseUrl(): string
    {
        return $this->frontend_base_url;
    }

    /**
     * Get the JWT secret key
     * @return string JWT secret key
     */
    public function jwtSecretKey(): string
    {
        return $this->preview_token['secret_key'];
    }

    /**
     * Get the JWT token expiry time
     * @return int JWT token expiry time
     */
    public function jwtExpiryTime(): int
    {
        return $this->preview_token['expiry_time'];
    }

    /**
     * Get the path mapping for a post type and type (publish or preview)
     */
    public function getPathMapping(string $post_type, string $type): string | null
    {
        if ($type !== 'publish' && $type !== 'preview') {
            // Fallback to publish if the type is not publish or preview
            $type = 'publish';
        }

        if (isset($this->path_mappings[$post_type][$type])) {
            return $this->path_mappings[$post_type][$type];
        } else {
            return null;
        }
    }

    /**
     * Get the disable permalink rewrite option
     */
    public function disablePermalinkRewriteOption(): bool
    {
        return $this->disable_permalink_rewrite;
    }

    /**
     * Get singleton instance
     * @return PluginSettings
     */
    public static function get(): PluginSettings
    {
        if (!isset(self::$instance) || (defined('PHPUNIT') && constant('PHPUNIT'))) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get frontend base URL from the WACK_PREVIEW_SETTINGS constant or plugin settings in the database
     *
     * Always use the value from the constant if it exists.
     */
    public static function getFrontendBaseUrl(): string
    {
        $frontend_base_url = self::getFrontendBaseUrlFromConstant();

        if (is_null($frontend_base_url)) {
            $frontend_base_url = self::getFrontendBaseUrlFromDatabase();
        }

        return $frontend_base_url ?? '';
    }

    /**
     * Get frontend base URL from the WACK_PREVIEW_SETTINGS constant
     */
    public static function getFrontendBaseUrlFromConstant(): string | null
    {
        $frontend_base_url = null;

        if (isset(Constants::settingsConstant()['frontend_base_url'])) {
            $frontend_base_url = Constants::settingsConstant()['frontend_base_url'];
        }

        return $frontend_base_url;
    }

    /**
     * Get frontend base URL from plugin settings in the database
     */
    public static function getFrontendBaseUrlFromDatabase(): string | null
    {
        $frontend_base_url = null;
        $settings_option = get_option('wack_preview_settings');

        if ($settings_option && isset($settings_option['frontend_base_url'])) {
            $frontend_base_url = $settings_option['frontend_base_url'];
        }

        return $frontend_base_url;
    }

    /**
     * Get Preview Token settings
     *
     * The values from the WACK_PREVIEW_SETTINGS constant will override the value from the database.
     * If the values are not set in both the constant and the database, use the default values.
     */
    public static function getPreviewToken(): array
    {
        $preview_token = self::getPreviewTokenFromDatabase() ?? [];
        $preview_token_from_constant = self::getPreviewTokenFromConstant() ?? [];
        $preview_token = array_merge(
            $preview_token,
            $preview_token_from_constant,
        );

        return array_merge([
            'secret_key' => 'THIS_IS_A_DEFAULT_SECRET_KEY', // default to this value
            'expiry_time' => 60 * 60 * 24 * 7, // default to 1 week
        ], $preview_token);
    }

    /**
     * Get Preview Token settings from the WACK_PREVIEW_SETTINGS constant
     */
    public static function getPreviewTokenFromConstant(): array | null
    {
        $preview_token = null;

        if (isset(Constants::settingsConstant()['preview_token'])) {
            $preview_token = Constants::settingsConstant()['preview_token'];
        }

        return $preview_token;
    }

    /**
     * Get Preview Token settings from plugin settings in the database
     */
    public static function getPreviewTokenFromDatabase(): array | null
    {
        $preview_token = null;
        $settings_option = get_option('wack_preview_settings');

        if ($settings_option && isset($settings_option['preview_token'])) {
            $preview_token = $settings_option['preview_token'];
        }

        return $preview_token;
    }

    /**
     * Get the path mappings from the WACK_PREVIEW_SETTINGS constant or plugin settings in the database
     *
     * Always use the value from the constant if it exists.
     */
    public static function getPathMappings(): array
    {
        $path_mappings = self::getPathMappingsFromConstant() ?? [];
        $path_mappings_from_database = self::getPathMappingsFromDatabase() ?? [];

        foreach ($path_mappings_from_database as $key => $value) {
            if (isset($path_mappings[$key])) {
                foreach ($value as $subKey => $subValue) {
                    if (!isset($path_mappings[$key][$subKey])) {
                        $path_mappings[$key][$subKey] = $subValue;
                    }
                }
            } else {
                $path_mappings[$key] = $value;
            }
        }

        return $path_mappings;
    }

    /**
     * Get the path mappings from the WACK_PREVIEW_SETTINGS constant
     */
    public static function getPathMappingsFromConstant(): array | null
    {
        $path_mappings = null;

        if (isset(Constants::settingsConstant()['path_mappings'])) {
            $path_mappings = Constants::settingsConstant()['path_mappings'];
            return $path_mappings;
        }

        return $path_mappings;
    }

    /**
     * Get the path mappings from plugin settings in the database
     */
    public static function getPathMappingsFromDatabase(): array | null
    {
        $path_mappings = null;
        $settings_option = get_option('wack_preview_settings');

        if ($settings_option && isset($settings_option['path_mappings'])) {
            $path_mappings = $settings_option['path_mappings'];
        }

        return $path_mappings;
    }

    /**
     * Get the disable permalink rewrite option from the WACK_PREVIEW_SETTINGS constant or plugin settings in the database
     *
     * Always use the value from the constant if it exists.
     */
    public static function getDisablePermalinkRewriteOption(): bool
    {
        $disable_permalink_rewrite = self::getDisablePermalinkRewriteOptionFromConstant();

        if ($disable_permalink_rewrite === true) {
            return true;
        } elseif ($disable_permalink_rewrite === false) {
            return false;
        } else {
            return self::getDisablePermalinkRewriteOptionFromDatabase();
        }
    }

    /**
     * Get the disable permalink rewrite option from the WACK_PREVIEW_SETTINGS constant
     */
    public static function getDisablePermalinkRewriteOptionFromConstant(): bool | null
    {
        if (!isset(Constants::settingsConstant()['advanced_settings']['disable_permalink_rewrite'])) {
            return null;
        }

        if (Constants::settingsConstant()['advanced_settings']['disable_permalink_rewrite'] === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the disable permalink rewrite option from plugin settings in the database
     */
    public static function getDisablePermalinkRewriteOptionFromDatabase(): bool
    {
        $settings_option = get_option('wack_preview_settings');

        if ($settings_option && isset($settings_option['advanced_settings']['disable_permalink_rewrite'])) {
            return $settings_option['advanced_settings']['disable_permalink_rewrite'];
        }

        return false;
    }
}
