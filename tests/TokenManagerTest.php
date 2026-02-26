<?php

namespace WackPreviewTest;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Mockery;
use WP_Mock;
use WP_Post;
use WackPreview\Constants;
use WackPreview\TokenManager;

final class TokenManagerTest extends WP_Mock\Tools\TestCase
{
    // HMAC-SHA256 には 32 バイト以上の鍵が必要なため、十分な長さを確保する
    private const TEST_SECRET_KEY = 'test-secret-key-for-unit-tests-!!';
    private const TEST_EXPIRY_TIME = 3600;

    /**
     * TokenManager 用の PluginSettings をモックするヘルパー
     */
    private function mockPluginSettings(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([
                'preview_token' => [
                    'secret_key' => self::TEST_SECRET_KEY,
                    'expiry_time' => self::TEST_EXPIRY_TIME,
                ],
            ]);

        WP_Mock::userFunction('get_option')
            ->with('wack_preview_settings')
            ->andReturn([]);
    }

    /**
     * WP_Post インスタンスを作成するヘルパー
     */
    private function createPost(int $id, string $post_name = ''): WP_Post
    {
        /** @var WP_Post&\Mockery\MockInterface $post */
        $post = Mockery::mock(WP_Post::class);
        $post->ID = $id;
        $post->post_name = $post_name;

        return $post;
    }

    //==========================================================================
    // generateToken
    //==========================================================================

    // phpcs:ignore
    public function test_generateToken_with_type_id(): void
    {
        $this->mockPluginSettings();
        $post = $this->createPost(123, 'my-slug');

        $manager = new TokenManager();
        $token = $manager->generateToken($post, 'id');

        $decoded = JWT::decode($token, new Key(self::TEST_SECRET_KEY, 'HS256'));

        // type が 'id' の場合、sub には投稿 ID が設定される
        $this->assertSame(123, $decoded->sub);
        $this->assertSame('wack-preview', $decoded->iss);
    }

    // phpcs:ignore
    public function test_generateToken_with_type_slug_and_slug_exists(): void
    {
        $this->mockPluginSettings();
        $post = $this->createPost(123, 'my-slug');

        $manager = new TokenManager();
        $token = $manager->generateToken($post, 'slug');

        $decoded = JWT::decode($token, new Key(self::TEST_SECRET_KEY, 'HS256'));

        // type が 'slug' でスラッグが設定されている場合、sub にはスラッグが設定される
        $this->assertSame('my-slug', $decoded->sub);
        $this->assertSame('wack-preview', $decoded->iss);
    }

    // phpcs:ignore
    public function test_generateToken_with_type_slug_but_empty_post_name(): void
    {
        $this->mockPluginSettings();
        $post = $this->createPost(456, '');

        $manager = new TokenManager();
        $token = $manager->generateToken($post, 'slug');

        $decoded = JWT::decode($token, new Key(self::TEST_SECRET_KEY, 'HS256'));

        // type が 'slug' でスラッグが空の場合、sub には投稿 ID が設定される（フォールバック）
        $this->assertSame(456, $decoded->sub);
    }

    // phpcs:ignore
    public function test_generateToken_contains_expiry_time(): void
    {
        $this->mockPluginSettings();
        $post = $this->createPost(123);

        $before = time();
        $manager = new TokenManager();
        $token = $manager->generateToken($post, 'id');
        $after = time();

        $decoded = JWT::decode($token, new Key(self::TEST_SECRET_KEY, 'HS256'));

        // exp には現在時刻 + 有効期限が設定される
        $this->assertGreaterThanOrEqual($before + self::TEST_EXPIRY_TIME, $decoded->exp);
        $this->assertLessThanOrEqual($after + self::TEST_EXPIRY_TIME, $decoded->exp);
    }

    //==========================================================================
    // verifyToken
    //==========================================================================

    // phpcs:ignore
    public function test_verifyToken_returns_true_for_valid_token(): void
    {
        $this->mockPluginSettings();
        $post = $this->createPost(123, 'my-slug');

        $manager = new TokenManager();
        $token = $manager->generateToken($post, 'id');

        $this->assertTrue($manager->verifyToken($token));
    }

    // phpcs:ignore
    public function test_verifyToken_returns_false_for_invalid_token(): void
    {
        $this->mockPluginSettings();

        $manager = new TokenManager();

        $this->assertFalse($manager->verifyToken('this-is-not-a-valid-jwt'));
    }

    // phpcs:ignore
    public function test_verifyToken_returns_false_for_expired_token(): void
    {
        $this->mockPluginSettings();

        // 1 時間前に期限切れになるトークンを手動で生成
        $expired_payload = [
            'sub' => 123,
            'iss' => 'wack-preview',
            'iat' => time() - 7200,
            'exp' => time() - 3600,
        ];
        $expired_token = JWT::encode($expired_payload, self::TEST_SECRET_KEY, 'HS256');

        $manager = new TokenManager();

        $this->assertFalse($manager->verifyToken($expired_token));
    }

    // phpcs:ignore
    public function test_verifyToken_returns_false_for_token_signed_with_wrong_key(): void
    {
        $this->mockPluginSettings();

        // 異なる秘密鍵で署名されたトークンを生成
        $payload = [
            'sub' => 123,
            'iss' => 'wack-preview',
            'iat' => time(),
            'exp' => time() + 3600,
        ];
        // 32 バイト以上の別の鍵で署名する
        $token_with_wrong_key = JWT::encode($payload, 'wrong-secret-key-that-is-long-!!!', 'HS256');

        $manager = new TokenManager();

        $this->assertFalse($manager->verifyToken($token_with_wrong_key));
    }
}
