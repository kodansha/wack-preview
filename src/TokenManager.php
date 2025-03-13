<?php

namespace WackPreview;

use WP_Post;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Class TokenManager
 *
 * @package WackPreview
 */
final class TokenManager
{
    private string $key;
    private int $expiry_time;
    private string $issuer = 'wack-preview';

    public function __construct()
    {
        $this->key = PluginSettings::get()->jwtSecretKey();
        $this->expiry_time = PluginSettings::get()->jwtExpiryTime();
    }

    /**
     * Generate a preview token (JWT) which includes post ID or slug
     *
     * JWT payload example:
     * {
     *   // Post ID (or post slug if "%slug%" template is set in the path mappings)
     *   // Note: If the post does not have the slug, it will fallback to the post ID even if the "%slug%" template is set.
     *   "sub": 123,
     *   // Issuer (always set to `wack-preview`)
     *   "iss": "wack-preview",
     *   // Issued at (UNIX timestamp)
     *   "iat": 1630000000,
     *   // Expiry time (UNIX timestamp)
     *   "exp": 1630003600
     * }
     *
     * @param WP_Post $post
     * @param string $type 'id' or 'slug'
     *
     * @return string
     */
    public function generateToken(WP_Post $post, string $type): string
    {
        $subject = $post->ID;

        // Fallback to ID if the post slug is empty even if the type is 'slug'.
        if ($type === 'slug' && !empty($post->post_name)) {
            $subject = $post->post_name;
        }

        $time = time();
        $payload = [
            'sub' => $subject,
            'iss' => $this->issuer,
            'iat' => $time,
            'exp' => $time + $this->expiry_time,
        ];

        return JWT::encode($payload, $this->key, 'HS256');
    }

    /**
     * Verify a JWT token
     *
     * @param string $token
     *
     * @return bool
     */
    public function verifyToken(string $token): bool
    {
        try {
            JWT::decode($token, new Key($this->key, 'HS256'));
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
