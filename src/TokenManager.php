<?php

namespace WackPreview;

use WP_Post;
use Firebase\JWT\JWT;

/**
 * Class TokenManager
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
     * Generate a JWT token which includes post ID or alug
     * @param WP_Post $post
     * @param string $type 'id' or 'slug'
     * @return string
     */
    public function generateToken(WP_Post $post, string $type): string
    {
        $subject = $type === 'slug' ? $post->post_name : $post->ID; // fallback to ID
        $time = time();
        $payload = [
            'sub' => $subject,
            'iss' => $this->issuer,
            'iat' => $time,
            'exp' => $time + $this->expiry_time,
        ];

        return JWT::encode($payload, $this->key, 'HS256');
    }
}
