<?php

namespace WackPreview;

/**
 * Class Utility
 *
 * @package WackPreview
 */
final class Utility
{
    /**
     * Retrieves all registered post types (except default post types).
     *
     * @return array An array of registered post type objects.
     */
    public static function getPostTypes(): array
    {
        $post_types = get_post_types(['public' => true], 'objects');

        $default_post_types = [
            // 'post',
            'page',
            'attachment',
            'revision',
            'nav_menu_item',
            'wp_template',
            'wp_template_part',
        ];

        return array_filter(
            $post_types,
            fn($post_type) => !in_array($post_type->name, $default_post_types),
        );
    }
}
