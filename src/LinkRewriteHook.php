<?php

namespace WackPreview;

use WP_Post;
use WP_REST_Response;

/**
 * Class LinkRewriteHook
 *
 * @package WackPreview
 */
final class LinkRewriteHook
{
    /**
     * Register hooks
     * This should be invoked on bootstrapping
     */
    public function init(): void
    {
        add_filter('post_link', [$this, 'rewritePermalink'], 10, 2);
        add_filter('post_type_link', [$this, 'rewritePermalink'], 10, 2);
        add_filter('preview_post_link', [$this, 'rewritePreviewLink'], 10, 2);
        add_action('init', [$this, 'hackFixDraftPreviewLinks'], 20);
    }

    /**
     * Rewrite permalink to frontend public URL
     * This behavior can be disabled by setting 'disable_permalink_rewrite' to true in plugin settings
     *
     * @param string $post_link
     * @param WP_Post $post
     *
     * @return string
     */
    public function rewritePermalink(string $post_link, WP_Post $post): string
    {
        if (PluginSettings::get()->disablePermalinkRewriteOption()) {
            return $post_link;
        }

        $path = PluginSettings::get()->getPathMapping($post->post_type, 'publish');

        if (is_null($path)) {
            return $post_link;
        }

        return $this->generatePublicUrl($path, $post);
    }

    /**
     * Rewrite preview link to frontend preview URL
     *
     * @param string $preview_link
     * @param WP_Post $post
     *
     * @return string rewritten preview link
     */
    public function rewritePreviewLink(string $preview_link, WP_Post $post): string
    {
        $path = PluginSettings::get()->getPathMapping($post->post_type, 'preview');

        if (is_null($path)) {
            return $preview_link;
        }

        return $this->generatePreviewUrl($path, $post);
    }

    /**
     * Generate a public URL for a published post
     *
     * @param string $frontend_publish_path
     * @param WP_Post $post
     *
     * @return string
     */
    private function generatePublicUrl(string $frontend_publish_path, WP_Post $post): string
    {
        $path = $this->replacePlaceholder($frontend_publish_path, $post);

        return PluginSettings::get()->frontendBaseUrl() . $path;
    }

    /**
     * Generate URL for previewing a post
     *
     * @param string  $frontend_path
     * @param WP_Post $post
     *
     * @return string
     */
    private function generatePreviewUrl(string $frontend_path, WP_Post $post): string
    {
        // Determine post ID or slug, depending on the path includes %id% or %slug%
        $type = str_contains($frontend_path, '%slug%') ? 'slug' : 'id';

        // Generate a JWT token
        $token_manager = new TokenManager();
        $token = $token_manager->generateToken($post, $type);

        // Replace placeholder with an actual post id or slug
        $replaced_path = $this->replacePlaceholder($frontend_path, $post);
        $parsed = parse_url($replaced_path);

        // Normalize path
        $path = '/' . ltrim($parsed['path'], '/');

        $query = isset($parsed['query'])
            ? '?' . $parsed['query'] . '&preview=true&preview_token=' . $token
            : '?preview=true&preview_token=' . $token;

        return PluginSettings::get()->frontendBaseUrl() . $path . $query;
    }

    /**
     * Replace placeholder with an actual post id or slug
     *
     * - %id% will be replaced with the post ID
     * - %slug% will be replaced with the post slug
     * - If the placeholder is not found, the original path will be returned
     * - If the post does not have the slug, it will fallback to the post ID
     *
     * @param string $path_with_placeholder
     * @param WP_Post $post
     *
     * @return string
     */
    private function replacePlaceholder(string $path_with_placeholder, WP_Post $post): string
    {
        $temp_string = str_replace('%id%', $post->ID, $path_with_placeholder);

        if (empty($post->post_name)) {
            return str_replace('%slug%', $post->ID, $temp_string);
        } else {
            return str_replace('%slug%', $post->post_name, $temp_string);
        }
    }

    /**
     * A small hack is needed to rewrite the preview link for draft articles.
     * this must be removed when wordpress do the properly fix https://github.com/WordPress/gutenberg/issues/13998
     */
    public function hackFixDraftPreviewLinks()
    {
        $post_types = Utility::getPostTypes();

        foreach ($post_types as $post_type) {
            add_filter('rest_prepare_' . $post_type->name, [$this, 'fixPreviewLinkInRestResponse'], 10, 2);
        }
    }

    /**
     * Hack Function that changes the preview link for draft articles,
     *
     * @param WP_REST_Response $response
     * @param WP_Post $post
     *
     * @return mixed
     */
    public function fixPreviewLinkInRestResponse(WP_REST_Response $response, WP_Post $post)
    {
        if ($post->post_status === 'draft') {
            $response->data['link'] = get_preview_post_link($post);
        }

        return $response;
    }
}
