<?php

namespace WackPreviewTest;

use WP_Mock;
use WackPreview\Utility;

final class UtilityTest extends WP_Mock\Tools\TestCase
{
    //==========================================================================
    // getPostTypes
    //==========================================================================

    // phpcs:ignore
    public function test_getPostTypes_excludes_default_post_types(): void
    {
        WP_Mock::userFunction('get_post_types')
            ->once()
            ->with(['public' => true], 'objects')
            ->andReturn([
                'page'       => (object) ['name' => 'page'],
                'attachment' => (object) ['name' => 'attachment'],
                'custom'     => (object) ['name' => 'custom'],
            ]);

        $result = Utility::getPostTypes();

        // デフォルト記事タイプは除外され、カスタム記事タイプのみが返る
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('custom', $result);
        $this->assertArrayNotHasKey('page', $result);
        $this->assertArrayNotHasKey('attachment', $result);
    }

    // phpcs:ignore
    public function test_getPostTypes_includes_post_type(): void
    {
        WP_Mock::userFunction('get_post_types')
            ->once()
            ->with(['public' => true], 'objects')
            ->andReturn([
                'post'   => (object) ['name' => 'post'],
                'page'   => (object) ['name' => 'page'],
                'custom' => (object) ['name' => 'custom'],
            ]);

        $result = Utility::getPostTypes();

        // 'post' タイプはデフォルト除外リストから外れているため、結果に含まれる
        $this->assertArrayHasKey('post', $result);
        $this->assertArrayHasKey('custom', $result);
        $this->assertArrayNotHasKey('page', $result);
    }

    // phpcs:ignore
    public function test_getPostTypes_returns_empty_array_when_all_are_default(): void
    {
        WP_Mock::userFunction('get_post_types')
            ->once()
            ->with(['public' => true], 'objects')
            ->andReturn([
                'page'          => (object) ['name' => 'page'],
                'attachment'    => (object) ['name' => 'attachment'],
                'nav_menu_item' => (object) ['name' => 'nav_menu_item'],
                'wp_template'   => (object) ['name' => 'wp_template'],
            ]);

        $result = Utility::getPostTypes();

        // すべてデフォルト記事タイプの場合は空の配列が返る
        $this->assertEmpty($result);
    }

    // phpcs:ignore
    public function test_getPostTypes_returns_empty_array_when_no_post_types_registered(): void
    {
        WP_Mock::userFunction('get_post_types')
            ->once()
            ->with(['public' => true], 'objects')
            ->andReturn([]);

        $result = Utility::getPostTypes();

        // 登録された記事タイプが存在しない場合は空の配列が返る
        $this->assertEmpty($result);
    }
}
