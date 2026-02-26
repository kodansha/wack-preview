<?php

namespace WackPreviewTest;

use Mockery;
use WP_Mock;
use WP_Post;
use WP_REST_Response;
use WackPreview\Constants;
use WackPreview\LinkRewriteHook;

final class LinkRewriteHookTest extends WP_Mock\Tools\TestCase
{
    /**
     * WP_Post インスタンスを作成するヘルパー
     */
    private function createPost(
        int $id,
        string $post_name = '',
        string $post_type = 'post',
        string $post_status = 'publish',
    ): WP_Post {
        /** @var WP_Post&\Mockery\MockInterface $post */
        $post = Mockery::mock(WP_Post::class);
        $post->ID = $id;
        $post->post_name = $post_name;
        $post->post_type = $post_type;
        $post->post_status = $post_status;

        return $post;
    }

    /**
     * PluginSettings のモックを設定するヘルパー
     *
     * @param array       $constants  WACK_PREVIEW_SETTINGS 定数の値
     * @param array|false $db_settings データベースの設定値
     */
    private function mockSettings(array $constants = [], array|false $db_settings = false): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn($constants);

        WP_Mock::userFunction('get_option')
            ->with('wack_preview_settings')
            ->andReturn($db_settings);
    }

    //==========================================================================
    // init
    //==========================================================================

    // phpcs:ignore
    public function test_init_registers_hooks(): void
    {
        // WP_Mock の期待値として検証するため、明示的なアサーションは不要
        $this->expectNotToPerformAssertions();

        $hook = new LinkRewriteHook();

        WP_Mock::expectFilterAdded('post_link', [$hook, 'rewritePermalink'], 10, 2);
        WP_Mock::expectFilterAdded('post_type_link', [$hook, 'rewritePermalink'], 10, 2);
        WP_Mock::expectFilterAdded('preview_post_link', [$hook, 'rewritePreviewLink'], 10, 2);
        WP_Mock::expectActionAdded('init', [$hook, 'hackFixDraftPreviewLinks'], 20);

        $hook->init();
    }

    //==========================================================================
    // rewritePermalink
    //==========================================================================

    // phpcs:ignore
    public function test_rewritePermalink_returns_original_when_disabled(): void
    {
        $this->mockSettings([
            'advanced_settings' => ['disable_permalink_rewrite' => true],
        ]);

        $post = $this->createPost(123, 'my-slug', 'post');
        $hook = new LinkRewriteHook();
        $original_link = 'https://wordpress.example.com/my-slug/';

        // permalink の書き換えが無効化されている場合は元の URL をそのまま返す
        $result = $hook->rewritePermalink($original_link, $post);

        $this->assertSame($original_link, $result);
    }

    // phpcs:ignore
    public function test_rewritePermalink_returns_original_when_no_path_mapping(): void
    {
        $this->mockSettings([
            'frontend_base_url' => 'https://frontend.example.com',
        ]);

        $post = $this->createPost(123, 'my-slug', 'post');
        $hook = new LinkRewriteHook();
        $original_link = 'https://wordpress.example.com/my-slug/';

        // post type のパスマッピングが存在しない場合は元の URL を返す
        $result = $hook->rewritePermalink($original_link, $post);

        $this->assertSame($original_link, $result);
    }

    // phpcs:ignore
    public function test_rewritePermalink_with_id_placeholder(): void
    {
        $this->mockSettings([
            'frontend_base_url' => 'https://frontend.example.com',
            'path_mappings' => [
                'post' => ['publish' => '/post/%id%'],
            ],
        ]);

        $post = $this->createPost(123, 'my-slug', 'post');
        $hook = new LinkRewriteHook();

        // %id% プレースホルダーが投稿 ID に置換される
        $result = $hook->rewritePermalink('https://wordpress.example.com/my-slug/', $post);

        $this->assertSame('https://frontend.example.com/post/123', $result);
    }

    // phpcs:ignore
    public function test_rewritePermalink_with_slug_placeholder(): void
    {
        $this->mockSettings([
            'frontend_base_url' => 'https://frontend.example.com',
            'path_mappings' => [
                'news' => ['publish' => '/news/%slug%'],
            ],
        ]);

        $post = $this->createPost(456, 'my-news-slug', 'news');
        $hook = new LinkRewriteHook();

        // %slug% プレースホルダーが投稿スラッグに置換される
        $result = $hook->rewritePermalink('https://wordpress.example.com/my-news-slug/', $post);

        $this->assertSame('https://frontend.example.com/news/my-news-slug', $result);
    }

    // phpcs:ignore
    public function test_rewritePermalink_falls_back_to_id_when_slug_is_empty(): void
    {
        $this->mockSettings([
            'frontend_base_url' => 'https://frontend.example.com',
            'path_mappings' => [
                'news' => ['publish' => '/news/%slug%'],
            ],
        ]);

        $post = $this->createPost(789, '', 'news');
        $hook = new LinkRewriteHook();

        // スラッグが空の場合は %slug% プレースホルダーを投稿 ID に置換する
        $result = $hook->rewritePermalink('https://wordpress.example.com/?p=789', $post);

        $this->assertSame('https://frontend.example.com/news/789', $result);
    }

    //==========================================================================
    // rewritePreviewLink
    //==========================================================================

    // phpcs:ignore
    public function test_rewritePreviewLink_returns_original_when_no_path_mapping(): void
    {
        $this->mockSettings([
            'frontend_base_url' => 'https://frontend.example.com',
        ]);

        $post = $this->createPost(123, 'my-slug', 'post');
        $hook = new LinkRewriteHook();
        $original_link = 'https://wordpress.example.com/?p=123&preview=true';

        // post type のパスマッピングが存在しない場合は元のプレビュー URL を返す
        $result = $hook->rewritePreviewLink($original_link, $post);

        $this->assertSame($original_link, $result);
    }

    // phpcs:ignore
    public function test_rewritePreviewLink_with_id_placeholder(): void
    {
        $this->mockSettings([
            'frontend_base_url' => 'https://frontend.example.com',
            'preview_token' => [
                'secret_key' => 'test-secret-key-for-unit-tests-!!',
                'expiry_time' => 3600,
            ],
            'path_mappings' => [
                'post' => ['preview' => '/post/preview/%id%'],
            ],
        ]);

        $post = $this->createPost(123, 'my-slug', 'post');
        $hook = new LinkRewriteHook();

        $result = $hook->rewritePreviewLink('https://wordpress.example.com/?p=123&preview=true', $post);

        // %id% プレースホルダーが投稿 ID に置換され、JWT トークンがクエリパラメータに付与される
        $this->assertStringStartsWith(
            'https://frontend.example.com/post/preview/123?preview=true&preview_token=',
            $result,
        );
    }

    // phpcs:ignore
    public function test_rewritePreviewLink_with_slug_placeholder(): void
    {
        $this->mockSettings([
            'frontend_base_url' => 'https://frontend.example.com',
            'preview_token' => [
                'secret_key' => 'test-secret-key-for-unit-tests-!!',
                'expiry_time' => 3600,
            ],
            'path_mappings' => [
                'news' => ['preview' => '/news/preview/%slug%'],
            ],
        ]);

        $post = $this->createPost(456, 'my-news-slug', 'news');
        $hook = new LinkRewriteHook();

        $result = $hook->rewritePreviewLink('https://wordpress.example.com/?p=456&preview=true', $post);

        // %slug% プレースホルダーがスラッグに置換され、JWT トークンがクエリパラメータに付与される
        $this->assertStringStartsWith(
            'https://frontend.example.com/news/preview/my-news-slug?preview=true&preview_token=',
            $result,
        );
    }

    // phpcs:ignore
    public function test_rewritePreviewLink_with_slug_placeholder_but_empty_slug(): void
    {
        $this->mockSettings([
            'frontend_base_url' => 'https://frontend.example.com',
            'preview_token' => [
                'secret_key' => 'test-secret-key-for-unit-tests-!!',
                'expiry_time' => 3600,
            ],
            'path_mappings' => [
                'news' => ['preview' => '/news/preview/%slug%'],
            ],
        ]);

        $post = $this->createPost(789, '', 'news');
        $hook = new LinkRewriteHook();

        $result = $hook->rewritePreviewLink('https://wordpress.example.com/?p=789&preview=true', $post);

        // スラッグが空の場合は %slug% を投稿 ID に置換したプレビュー URL を生成する
        $this->assertStringStartsWith(
            'https://frontend.example.com/news/preview/789?preview=true&preview_token=',
            $result,
        );
    }

    //==========================================================================
    // hackFixDraftPreviewLinks
    //==========================================================================

    // phpcs:ignore
    public function test_hackFixDraftPreviewLinks_registers_rest_prepare_filters(): void
    {
        // WP_Mock の期待値として検証するため、明示的なアサーションは不要
        $this->expectNotToPerformAssertions();

        WP_Mock::userFunction('get_post_types')
            ->once()
            ->with(['public' => true], 'objects')
            ->andReturn([
                'custom_post' => (object) ['name' => 'custom_post'],
                'news'        => (object) ['name' => 'news'],
            ]);

        $hook = new LinkRewriteHook();

        // 各カスタム記事タイプに対して rest_prepare_{post_type} フィルターが登録される
        WP_Mock::expectFilterAdded('rest_prepare_custom_post', [$hook, 'fixPreviewLinkInRestResponse'], 10, 2);
        WP_Mock::expectFilterAdded('rest_prepare_news', [$hook, 'fixPreviewLinkInRestResponse'], 10, 2);

        $hook->hackFixDraftPreviewLinks();
    }

    //==========================================================================
    // fixPreviewLinkInRestResponse
    //==========================================================================

    // phpcs:ignore
    public function test_fixPreviewLinkInRestResponse_updates_link_for_draft_post(): void
    {
        $post = $this->createPost(123, 'my-slug', 'post', 'draft');

        /** @var WP_REST_Response&\Mockery\MockInterface $response */
        $response = Mockery::mock(WP_REST_Response::class);
        $response->data = ['link' => 'https://wordpress.example.com/?p=123'];

        WP_Mock::userFunction('get_preview_post_link')
            ->once()
            ->with($post)
            ->andReturn('https://wordpress.example.com/?p=123&preview=true');

        $hook = new LinkRewriteHook();

        // ドラフト記事の場合は REST レスポンスのリンクをプレビューリンクに更新する
        $result = $hook->fixPreviewLinkInRestResponse($response, $post);

        $this->assertSame('https://wordpress.example.com/?p=123&preview=true', $result->data['link']);
    }

    // phpcs:ignore
    public function test_fixPreviewLinkInRestResponse_returns_unchanged_for_published_post(): void
    {
        $post = $this->createPost(123, 'my-slug', 'post', 'publish');

        /** @var WP_REST_Response&\Mockery\MockInterface $response */
        $response = Mockery::mock(WP_REST_Response::class);
        $response->data = ['link' => 'https://frontend.example.com/post/123'];

        $hook = new LinkRewriteHook();

        // 公開済み記事の場合は REST レスポンスを変更せずに返す
        $result = $hook->fixPreviewLinkInRestResponse($response, $post);

        $this->assertSame('https://frontend.example.com/post/123', $result->data['link']);
    }
}
