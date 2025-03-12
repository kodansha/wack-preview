<?php

namespace WackPreview;

/**
 * Class AdminMenu
 *
 * @package WackPreview
 */
final class AdminMenu
{
    /**
     * Initialize the settings page
     */
    public function init()
    {
        add_action('admin_menu', [$this, 'addAdminMenuPage']);
        add_action('admin_menu', [$this, 'addAdminSubMenuPage']);
    }

    /**
     * Add the settings page to the WordPress admin menu
     */
    public function addAdminMenuPage(): void
    {
        global $menu;

        // Check if the menu already exists
        $menu_slug = 'wack-stack-settings';
        $menu_exists = false;

        foreach ($menu as $item) {
            if ($item[2] == $menu_slug) {
                $menu_exists = true;
                break;
            }
        }

        // Add the menu if it doesn't exist
        if (!$menu_exists) {
            add_menu_page(
                'WACK Stack Settings',
                'WACK Stack',
                'manage_options',
                $menu_slug,
                function () {
                    ?>
                    <div class="wrap">
                        <h1>WACK Stack Settings</h1>
                        <p>The settings pages for plugins belonging to the WACK Stack ecosystem.</p>
                    </div>
                    <?php
                },
                'dashicons-superhero-alt',
            );
        }
    }

    public function addAdminSubMenuPage(): void
    {
        add_submenu_page(
            'wack-stack-settings',
            'WACK Preview Settings',
            'WACK Preview',
            'manage_options',
            'wack-preview-settings',
            function () {
                ?>
                <div class="wrap">
                    <form action='options.php' method='post'>
                        <h1>WACK Preview Settings</h1>
                        <?php
                        settings_fields('wack-preview-settings');
                        do_settings_sections('wack-preview-settings-page');
                        submit_button();
                        ?>
                    </form>
                </div>
                <?php
            },
        );

        register_setting(
            'wack-preview-settings',
            'wack_preview_settings',
            ['sanitize_callback' => [$this, 'optionsSanitizeCallback']],
        );

        //----------------------------------------------------------------------
        // Frontend Base URL Section
        //----------------------------------------------------------------------
        add_settings_section(
            'wack-preview-settings-frontend-url-section',
            'Frontend URL',
            '__return_null',
            'wack-preview-settings-page',
        );

        // Frontend Base URL Field
        add_settings_field(
            'frontend_base_url',
            'Frontend Base URL',
            function () {
                $settings_option = get_option('wack_preview_settings');
                $frontend_base_url = $settings_option['frontend_base_url'] ?? '';
                ?>
                <input type="text" name="wack_preview_settings[frontend_base_url]" value="<?php echo $frontend_base_url; ?>">
                <p>Enter the base URL of your frontend to preview posts (e.g. <code>https://frontend.example.com</code>).</p>
                <?php
            },
            'wack-preview-settings-page',
            'wack-preview-settings-frontend-url-section',
        );

        //----------------------------------------------------------------------
        // Preview Token Section
        //----------------------------------------------------------------------
        add_settings_section(
            'wack-preview-settings-token-section',
            'Preview Token',
            '__return_null',
            'wack-preview-settings-page',
        );

        // Secret Key Field
        add_settings_field(
            'secret_key',
            'Secret Key',
            function () {
                $settings_option = get_option('wack_preview_settings');
                $secret_key = $settings_option['preview_token']['secret_key'] ?? '';
                ?>
                <input type="text" name="wack_preview_settings[preview_token][secret_key]" value="<?php echo $secret_key; ?>">
                <p>The secret key used to sign the preview token. Please keep this key secret.</p>
                <?php
            },
            'wack-preview-settings-page',
            'wack-preview-settings-token-section',
        );

        // Expiry Time Field
        add_settings_field(
            'expiry_time',
            'Expiry Time (seconds)',
            function () {
                $settings_option = get_option('wack_preview_settings');
                $expiry_time = $settings_option['preview_token']['expiry_time'] ?? '';
                ?>
                <input type="text" id="expiry_time" name="wack_preview_settings[preview_token][expiry_time]" value="<?php echo $expiry_time; ?>">
                <p>The expiry time of the preview token in seconds. (e.g. <code>3600</code>)</p>
                <?php
            },
            'wack-preview-settings-page',
            'wack-preview-settings-token-section',
        );

        //----------------------------------------------------------------------
        // Path Mappings Section
        //----------------------------------------------------------------------
        add_settings_section(
            'wack-preview-settings-mappings-section',
            'Path Mappings',
            function () {
                echo '<p>Enter the page paths for published content and preview content on the frontend. You can use two template strings in the paths:</p>';
                echo '<ul>';
                echo '<li><code>%id%</code> - Will be replaced with the actual post ID</li>';
                echo '<li><code>%slug%</code> - Will be replaced with the actual post slug</li>';
                echo '</ul>';
            },
            'wack-preview-settings-page',
        );

        // Generate Path Mappings Field
        $post_types = Utility::getPostTypes();

        foreach ($post_types as $post_type) {
            add_settings_field(
                'path_mappings_publish_' . $post_type->name,
                $post_type->label . ' (Published)',
                function () use ($post_type) {
                    $settings_option = get_option('wack_preview_settings');
                    $path_mappings = $settings_option['path_mappings'] ?? [];
                    $path_mapping = $path_mappings[$post_type->name] ?? [];
                    $path_mapping_publish = $path_mapping['publish'] ?? '';
                    ?>
                    <input type="text" name="wack_preview_settings[path_mappings][<?php echo $post_type->name; ?>][publish]" value="<?php echo $path_mapping_publish; ?>">
                    <?php
                },
                'wack-preview-settings-page',
                'wack-preview-settings-mappings-section',
            );

            add_settings_field(
                'path_mappings_preview_' . $post_type->name,
                $post_type->label . ' (Preview)',
                function () use ($post_type) {
                    $settings_option = get_option('wack_preview_settings');
                    $path_mappings = $settings_option['path_mappings'] ?? [];
                    $path_mapping = $path_mappings[$post_type->name] ?? [];
                    $path_mapping_preview = $path_mapping['preview'] ?? '';
                    ?>
                    <input type="text" name="wack_preview_settings[path_mappings][<?php echo $post_type->name; ?>][preview]" value="<?php echo $path_mapping_preview; ?>">
                    <?php
                },
                'wack-preview-settings-page',
                'wack-preview-settings-mappings-section',
            );
        }

        //----------------------------------------------------------------------
        // Advanced Settings Section
        //----------------------------------------------------------------------
        add_settings_section(
            'wack-preview-settings-advanced-section',
            'Advanced Settings',
            '__return_null',
            'wack-preview-settings-page',
        );

        // Disable Permalink Rewrite Field
        add_settings_field(
            'disable_permalink_rewrite',
            'Disable Permalink Rewrite',
            function () {
                $settings_option = get_option('wack_preview_settings');
                $disable_permalink_rewrite = $settings_option['advanced_settings']['disable_permalink_rewrite'] ?? false;
                ?>
                <input type="checkbox" name="wack_preview_settings[advanced_settings][disable_permalink_rewrite]" <?php echo $disable_permalink_rewrite ? 'checked' : ''; ?>>
                <p>If you do not want the post permalinks to be rewritten by the plugin, turn on this option.</p>
                <?php
            },
            'wack-preview-settings-page',
            'wack-preview-settings-advanced-section',
        );

        // Remove the default WACK Stack settings page
        remove_submenu_page('wack-stack-settings', 'wack-stack-settings');
    }

    /**
     * Render the sub menu page
     */
    public function renderSubMenuPage(): void
    {
        settings_errors();
        ?>
        <div class="wrap">
            <form action='options.php' method='post'>
                <h1>WACK Preview Settings</h1>
                <p><b>NOTE:</b> If the <code>WACK_PREVIEW_SETTINGS</code> constant provides the same setting options, the options entered here will be ignored.</p>
                <?php
                settings_fields('wack_preview_settings');
                do_settings_sections('wack_preview_settings_page');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Sanitize the options passed in
     *
     * - Convert string value in $options[preview_token][expiry_time] to integer
     */
    public function optionsSanitizeCallback($options): array | null
    {
        $errors = [];

        // Check if the frontend base URL is a valid URL starting with http:// or https://
        if (isset($options['frontend_base_url'])) {
            $frontend_base_url = $options['frontend_base_url'];
            if (filter_var($frontend_base_url, FILTER_VALIDATE_URL) === false || (strpos($frontend_base_url, 'http://') !== 0 && strpos($frontend_base_url, 'https://') !== 0)) {
                $errors[] = 'Frontend URL: Frontend Base URL must be a valid URL starting with http:// or https://.';
            }
        }

        // Check if the expiry time is an positive numeric string
        // If it is, convert it to an integer
        if (isset($options['preview_token']['expiry_time'])) {
            $expiry_time_str = (string) $options['preview_token']['expiry_time'];
            if (is_numeric($expiry_time_str) && (int) $expiry_time_str > 0) {
                $options['preview_token']['expiry_time'] = (int) $expiry_time_str;
            } else {
                $errors[] = 'Preview Token: Expiry Time must be a positive integer.';
            }
        }

        // Advanced settings - disable permalink rewrite option
        // Set the option value to boolean
        if (isset($options['advanced_settings']['disable_permalink_rewrite'])) {
            $options['advanced_settings']['disable_permalink_rewrite'] = true;
        } else {
            $options['advanced_settings']['disable_permalink_rewrite'] = false;
        }

        if (!empty($errors)) {
            $error_messages = '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>';
            add_settings_error(
                'wack_preview_settings',
                'wack_preview_settings_validation_errors',
                $error_messages,
            );

            // Restore the current option value
            $current_option_value = get_option('wack_preview_settings');
            if (empty($current_option_value)) {
                return null;
            }
            return $current_option_value;
        }

        return $options;
    }
}
