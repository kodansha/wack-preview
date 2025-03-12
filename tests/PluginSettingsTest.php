<?php

namespace WackPreviewTest;

use WP_Mock;
use Mockery;
use WackPreview\PluginSettings;
use WackPreview\Constants;

final class PluginSettingsTest extends WP_Mock\Tools\TestCase
{
    //==========================================================================
    // getFrontendBaseUrlFromConstant
    //==========================================================================
    // phpcs:ignore
    public function test_getFrontendBaseUrlFromConstant_settings_found(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([
                'frontend_base_url' => 'https://example.com',
            ]);
        $result = PluginSettings::getFrontendBaseUrlFromConstant();
        $this->assertSame('https://example.com', $result);
    }

    // phpcs:ignore
    public function test_getFrontendBaseUrlFromConstant_settings_not_found(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([]);
        $result = PluginSettings::getFrontendBaseUrlFromConstant();
        $this->assertNull($result);
    }

    //==========================================================================
    // getFrontendBaseUrlFromDatabase
    //==========================================================================
    // phpcs:ignore
    public function test_getFrontendBaseUrlFromDatabase_settings_found(): void
    {
        WP_Mock::userFunction('get_option')
            ->once()
            ->with('wack_preview_settings')
            ->andReturn([
                'frontend_base_url' => 'https://example.com',
            ]);
        $result = PluginSettings::getFrontendBaseUrlFromDatabase();
        $this->assertSame('https://example.com', $result);
    }

    // phpcs:ignore
    public function test_getFrontendBaseUrlFromDatabase_settings_found_but_invalid(): void
    {
        WP_Mock::userFunction('get_option')
            ->once()
            ->with('wack_preview_settings')
            ->andReturn([]);
        $result = PluginSettings::getFrontendBaseUrlFromDatabase();
        $this->assertNull($result);
    }

    // phpcs:ignore
    public function test_getFrontendBaseUrlFromDatabase_settings_not_found(): void
    {
        WP_Mock::userFunction('get_option')
            ->once()
            ->with('wack_preview_settings')
            ->andReturn(false);
        $result = PluginSettings::getFrontendBaseUrlFromDatabase();
        $this->assertNull($result);
    }

    //==========================================================================
    // getPreviewTokenFromConstant
    //==========================================================================
    // phpcs:ignore
    public function test_getPreviewTokenFromConstant_settings_found(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([
                'preview_token' => [
                    'secret_key' => 'test-secret-key',
                    'expiry_time' => 3600,
                ],
            ]);
        $result = PluginSettings::getPreviewTokenFromConstant();
        $this->assertSame([
            'secret_key' => 'test-secret-key',
            'expiry_time' => 3600,
        ], $result);
    }

    // phpcs:ignore
    public function test_getPreviewTokenFromConstant_settings_not_found(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([]);
        $result = PluginSettings::getPreviewTokenFromConstant();
        $this->assertNull($result);
    }

    //==========================================================================
    // getPreviewTokenFromDatabase
    //==========================================================================
    // phpcs:ignore
    public function test_getPreviewTokenFromDatabase_settings_found(): void
    {
        WP_Mock::userFunction('get_option')
            ->once()
            ->with('wack_preview_settings')
            ->andReturn([
                'preview_token' => [
                    'secret_key' => 'test-secret-key',
                    'expiry_time' => 3600,
                ],
            ]);
        $result = PluginSettings::getPreviewTokenFromDatabase();
        $this->assertSame([
            'secret_key' => 'test-secret-key',
            'expiry_time' => 3600,
        ], $result);
    }

    // phpcs:ignore
    public function test_getPreviewTokenFromDatabase_settings_found_but_invalid(): void
    {
        WP_Mock::userFunction('get_option')
            ->once()
            ->with('wack_preview_settings')
            ->andReturn([]);
        $result = PluginSettings::getPreviewTokenFromDatabase();
        $this->assertNull($result);
    }

    // phpcs:ignore
    public function test_getPreviewTokenFromDatabase_settings_not_found(): void
    {
        WP_Mock::userFunction('get_option')
            ->once()
            ->with('wack_preview_settings')
            ->andReturn(false);
        $result = PluginSettings::getPreviewTokenFromDatabase();
        $this->assertNull($result);
    }

    //==========================================================================
    // getPathMappingsFromConstant
    //==========================================================================
    // phpcs:ignore
    public function test_getPathMappingsFromConstant_settings_found(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([
                'path_mappings' => [
                    'post' => [
                        'publish' => '/post/%id%',
                        'preview' => '/post/preview/%id%',
                    ],
                    'news' => [
                        'publish' => '/news/%id%',
                        'preview' => '/news/preview/%id%',
                    ],
                ]
            ]);
        $result = PluginSettings::getPathMappingsFromConstant();
        $this->assertSame([
            'post' => [
                'publish' => '/post/%id%',
                'preview' => '/post/preview/%id%',
            ],
            'news' => [
                'publish' => '/news/%id%',
                'preview' => '/news/preview/%id%',
            ],
        ], $result);
    }

    // phpcs:ignore
    public function test_getPathMappingsFromConstant_settings_not_found(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([]);
        $result = PluginSettings::getPathMappingsFromConstant();
        $this->assertNull($result);
    }

    //==========================================================================
    // getPathMappingsFromDatabase
    //==========================================================================
    // phpcs:ignore
    public function test_getPathMappingsFromDatabase_settings_found(): void
    {
        WP_Mock::userFunction('get_option')
            ->once()
            ->with('wack_preview_settings')
            ->andReturn([
                'path_mappings' => [
                    'post' => [
                        'publish' => '/post/%id%',
                        'preview' => '/post/preview/%id%',
                    ],
                    'news' => [
                        'publish' => '/news/%id%',
                        'preview' => '/news/preview/%id%',
                    ],
                ]
            ]);
        $result = PluginSettings::getPathMappingsFromDatabase();
        $this->assertSame([
            'post' => [
                'publish' => '/post/%id%',
                'preview' => '/post/preview/%id%',
            ],
            'news' => [
                'publish' => '/news/%id%',
                'preview' => '/news/preview/%id%',
            ],
        ], $result);
    }

    // phpcs:ignore
    public function test_getPathMappingsFromDatabase_settings_found_but_invalid(): void
    {
        WP_Mock::userFunction('get_option')
            ->once()
            ->with('wack_preview_settings')
            ->andReturn([]);
        $result = PluginSettings::getPathMappingsFromDatabase();
        $this->assertNull($result);
    }

    // phpcs:ignore
    public function test_getPathMappingsFromDatabase_settings_not_found(): void
    {
        WP_Mock::userFunction('get_option')
            ->once()
            ->with('wack_preview_settings')
            ->andReturn(false);
        $result = PluginSettings::getPathMappingsFromDatabase();
        $this->assertNull($result);
    }

    //==========================================================================
    // getDisablePermalinkRewriteOptionFromConstant
    //==========================================================================
    // phpcs:ignore
    public function test_getDisablePermalinkRewriteOptionFromConstant_settings_found(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([
                'advanced_settings' => [
                    'disable_permalink_rewrite' => true,
                ]
            ]);
        $result = PluginSettings::getDisablePermalinkRewriteOptionFromConstant();
        $this->assertTrue($result);
    }

    // phpcs:ignore
    public function test_getDisablePermalinkRewriteOptionFromConstant_settings_found_but_not_true(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([
                'advanced_settings' => [
                    'disable_permalink_rewrite' => 'DUMMY',
                ]
            ]);
        $result = PluginSettings::getDisablePermalinkRewriteOptionFromConstant();
        $this->assertFalse($result);
    }

    // phpcs:ignore
    public function test_getDisablePermalinkRewriteOptionFromConstant_settings_not_found(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([]);
        $result = PluginSettings::getDisablePermalinkRewriteOptionFromConstant();
        $this->assertNull($result);
    }

    //==========================================================================
    // getDisablePermalinkRewriteOptionFromDatabase
    //==========================================================================
    // phpcs:ignore
    public function test_getDisablePermalinkRewriteOptionFromDatabase_settings_found(): void
    {
        WP_Mock::userFunction('get_option')
            ->once()
            ->with('wack_preview_settings')
            ->andReturn([
                'advanced_settings' => [
                    'disable_permalink_rewrite' => true,
                ]
            ]);
        $result = PluginSettings::getDisablePermalinkRewriteOptionFromDatabase();
        $this->assertTrue($result);
    }

    // phpcs:ignore
    public function test_getDisablePermalinkRewriteOptionFromDatabase_settings_not_found(): void
    {
        WP_Mock::userFunction('get_option')
            ->once()
            ->with('wack_preview_settings')
            ->andReturn(false);
        $result = PluginSettings::getDisablePermalinkRewriteOptionFromDatabase();
        $this->assertFalse($result);
    }

    //==========================================================================
    // frontendBaseUrl
    //==========================================================================
    // phpcs:ignore
    public function test_frontendBaseUrl_not_found(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([]);

        WP_Mock::userFunction('get_option')
            ->with('wack_preview_settings')
            ->andReturn([]);

        $instance = PluginSettings::get();

        $this->assertSame('', $instance->frontendBaseUrl());
    }

    // phpcs:ignore
    public function test_frontendBaseUrl_found_in_constant(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([
                'frontend_base_url' => 'https://frontend.example.com',
            ]);

        WP_Mock::userFunction('get_option')
            ->with('wack_preview_settings')
            ->andReturn([]);

        $instance = PluginSettings::get();

        $this->assertSame('https://frontend.example.com', $instance->frontendBaseUrl());
    }

    // phpcs:ignore
    public function test_frontendBaseUrl_only_found_in_database(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([]);

        WP_Mock::userFunction('get_option')
            ->with('wack_preview_settings')
            ->andReturn([
                'frontend_base_url' => 'https://frontend.example.com',
            ]);

        $instance = PluginSettings::get();

        $this->assertSame('https://frontend.example.com', $instance->frontendBaseUrl());
    }

    //==========================================================================
    // jwtSecretKey
    // jwtExpiryTime
    //==========================================================================
    // phpcs:ignore
    public function test_jwtSecretKey_jwtExpiryTime_not_found(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([]);

        WP_Mock::userFunction('get_option')
            ->with('wack_preview_settings')
            ->andReturn([]);

        $instance = PluginSettings::get();

        $this->assertSame('THIS_IS_A_DEFAULT_SECRET_KEY', $instance->jwtSecretKey());
        $this->assertSame(604800, $instance->jwtExpiryTime());
    }

    // phpcs:ignore
    public function test_jwtSecretKey_jwtExpiryTime_found_only_in_constant(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([
                'preview_token' => [
                    'secret_key' => 'test-secret-key',
                    'expiry_time' => 3600,
                ],
            ]);

        WP_Mock::userFunction('get_option')
            ->with('wack_preview_settings')
            ->andReturn([]);

        $instance = PluginSettings::get();

        $this->assertSame('test-secret-key', $instance->jwtSecretKey());
        $this->assertSame(3600, $instance->jwtExpiryTime());
    }

    // phpcs:ignore
    public function test_jwtSecretKey_jwtExpiryTime_found_only_in_database(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([]);

        WP_Mock::userFunction('get_option')
            ->with('wack_preview_settings')
            ->andReturn([
                'preview_token' => [
                    'secret_key' => 'test-secret-key',
                    'expiry_time' => 3600,
                ],
            ]);

        $instance = PluginSettings::get();

        $this->assertSame('test-secret-key', $instance->jwtSecretKey());
        $this->assertSame(3600, $instance->jwtExpiryTime());
    }

    // phpcs:ignore
    public function test_jwtSecretKey_jwtExpiryTime_found_in_both_constant_and_database(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([
                'preview_token' => [
                    'secret_key' => 'test-secret-key-constant',
                    'expiry_time' => 3600,
                ],
            ]);

        WP_Mock::userFunction('get_option')
            ->with('wack_preview_settings')
            ->andReturn([
                'preview_token' => [
                    'secret_key' => 'test-secret-key-database',
                    'expiry_time' => 7200,
                ],
            ]);

        $instance = PluginSettings::get();

        $this->assertSame('test-secret-key-constant', $instance->jwtSecretKey());
        $this->assertSame(3600, $instance->jwtExpiryTime());
    }

    //==========================================================================
    // getPathMapping
    //==========================================================================
    // phpcs:ignore
    public function test_getPathMapping_wrong_type(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([
                'path_mappings' => [
                    'post' => [
                        'publish' => '/post/%id%',
                        'preview' => '/post/preview/%id%',
                    ],
                ]
            ]);

        WP_Mock::userFunction('get_option')
            ->with('wack_preview_settings')
            ->andReturn([]);

        $instance = PluginSettings::get();

        $this->assertSame('/post/%id%', $instance->getPathMapping('post', 'wrong_type'));
    }

    // phpcs:ignore
    public function test_getPathMapping_not_found(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([]);
        WP_Mock::userFunction('get_option')
            ->with('wack_preview_settings')
            ->andReturn([]);

        $instance = PluginSettings::get();

        $this->assertNull($instance->getPathMapping('post', 'publish'));
    }

    // phpcs:ignore
    public function test_getPathMapping_overwrite_by_constant(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([
                'path_mappings' => [
                    'post' => [
                        'publish' => '/constant/post/%id%',
                    ],
                    'news' => [
                        'preview' => '/constant/news/preview/%id%',
                    ]
                ]
            ]);
        WP_Mock::userFunction('get_option')
            ->with('wack_preview_settings')
            ->andReturn([
                'path_mappings' => [
                    'post' => [
                        'publish' => '/database/post/%id%',
                        'preview' => '/database/post/preview/%id%',
                    ],
                    'news' => [
                        'publish' => '/database/news/%id%',
                        'preview' => '/database/news/preview/%id%',
                    ]
                ]
            ]);

        $instance = PluginSettings::get();

        $this->assertSame('/constant/post/%id%', $instance->getPathMapping('post', 'publish'));
        $this->assertSame('/database/post/preview/%id%', $instance->getPathMapping('post', 'preview'));
        $this->assertSame('/database/news/%id%', $instance->getPathMapping('news', 'publish'));
        $this->assertSame('/constant/news/preview/%id%', $instance->getPathMapping('news', 'preview'));
    }

    //==========================================================================
    // disablePermalinkRewriteOption
    //==========================================================================
    // phpcs:ignore
    public function test_disablePermalinkRewriteOption_found_in_constant(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([
                'advanced_settings' => [
                    'disable_permalink_rewrite' => true,
                ]
            ]);

        WP_Mock::userFunction('get_option')
            ->with('wack_preview_settings')
            ->andReturn([]);

        $instance = PluginSettings::get();

        $this->assertTrue($instance->disablePermalinkRewriteOption());
    }

    // phpcs:ignore
    public function test_disablePermalinkRewriteOption_constant_value_overwrite_database_value(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([
                'advanced_settings' => [
                    'disable_permalink_rewrite' => false,
                ]
            ]);

        WP_Mock::userFunction('get_option')
            ->with('wack_preview_settings')
            ->andReturn([
                'advanced_settings' => [
                    'disable_permalink_rewrite' => true,
                ]
            ]);

        $instance = PluginSettings::get();

        $this->assertFalse($instance->disablePermalinkRewriteOption());
    }

    // phpcs:ignore
    public function test_disablePermalinkRewriteOption_found_only_in_database(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([]);

        WP_Mock::userFunction('get_option')
            ->with('wack_preview_settings')
            ->andReturn([
                'advanced_settings' => [
                    'disable_permalink_rewrite' => true,
                ]
            ]);

        $instance = PluginSettings::get();

        $this->assertTrue($instance->disablePermalinkRewriteOption());
    }

    // phpcs:ignore
    public function test_disablePermalinkRewriteOption_found_only_in_database_and_false(): void
    {
        $mock = Mockery::mock('overload:' . Constants::class)->makePartial();
        $mock->shouldReceive('settingsConstant')
            ->andReturn([]);

        WP_Mock::userFunction('get_option')
            ->with('wack_preview_settings')
            ->andReturn([
                'advanced_settings' => [
                    'disable_permalink_rewrite' => false,
                ]
            ]);

        $instance = PluginSettings::get();

        $this->assertFalse($instance->disablePermalinkRewriteOption());
    }
}
