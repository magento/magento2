<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Model\Config;

use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration coverage for config placeholder processing when base URL is empty.
 *
 * @magentoAppIsolation enabled
 */
class PlaceholderTest extends TestCase
{
    /**
     * @var Placeholder
     */
    private $placeholder;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->placeholder = Bootstrap::getObjectManager()->create(
            Placeholder::class,
            [
                'urlPaths' => [
                    'unsecureBaseUrl' => Store::XML_PATH_UNSECURE_BASE_URL,
                    'secureBaseUrl' => Store::XML_PATH_SECURE_BASE_URL,
                ],
                'urlPlaceholder' => Store::BASE_URL_PLACEHOLDER,
            ]
        );
    }

    /**
     * Happy path: placeholders resolve using configured base URLs.
     *
     * @return void
     */
    public function testProcessResolvesBaseUrlPlaceholders(): void
    {
        $data = [
            'web' => [
                'unsecure' => [
                    'base_url' => 'http://example.test/',
                    'base_link_url' => '{{unsecure_base_url}}path/',
                ],
                'secure' => [
                    'base_url' => 'https://example.test/',
                    'base_link_url' => '{{secure_base_url}}path/',
                ],
            ],
        ];

        $result = $this->placeholder->process($data);

        $this->assertSame('http://example.test/path/', $result['web']['unsecure']['base_link_url']);
        $this->assertSame('https://example.test/path/', $result['web']['secure']['base_link_url']);
    }

    /**
     * NULL secure base URL must not recurse infinitely; a clear exception is required.
     *
     * @return void
     */
    public function testProcessThrowsWhenSecureBaseUrlIsNull(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            'Cannot resolve "{{secure_base_url}}" because "web/secure/base_url" is empty.'
        );

        $this->placeholder->process([
            'web' => [
                'unsecure' => [
                    'base_url' => 'http://example.test/',
                ],
                'secure' => [
                    'base_url' => null,
                    'base_link_url' => '{{secure_base_url}}',
                ],
            ],
        ]);
    }

    /**
     * Empty-string secure base URL must fail the same way as NULL.
     *
     * @return void
     */
    public function testProcessThrowsWhenSecureBaseUrlIsEmptyString(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            'Cannot resolve "{{secure_base_url}}" because "web/secure/base_url" is empty.'
        );

        $this->placeholder->process([
            'web' => [
                'unsecure' => [
                    'base_url' => 'http://example.test/',
                ],
                'secure' => [
                    'base_url' => '',
                    'base_link_url' => '{{secure_base_url}}checkout/',
                ],
            ],
        ]);
    }

    /**
     * Missing secure base URL path must not recurse when a dependent placeholder is processed.
     *
     * @return void
     */
    public function testProcessThrowsWhenSecureBaseUrlIsMissing(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            'Cannot resolve "{{secure_base_url}}" because "web/secure/base_url" is empty.'
        );

        $this->placeholder->process([
            'web' => [
                'unsecure' => [
                    'base_url' => 'http://example.test/',
                ],
                'secure' => [
                    'base_link_url' => '{{secure_base_url}}',
                ],
            ],
        ]);
    }

    /**
     * NULL unsecure base URL must produce a localizable, path-specific error.
     *
     * @return void
     */
    public function testProcessThrowsWhenUnsecureBaseUrlIsNull(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            'Cannot resolve "{{unsecure_base_url}}" because "web/unsecure/base_url" is empty.'
        );

        $this->placeholder->process([
            'web' => [
                'unsecure' => [
                    'base_url' => null,
                    'base_link_url' => '{{unsecure_base_url}}',
                ],
                'secure' => [
                    'base_url' => 'https://example.test/',
                ],
            ],
        ]);
    }

    /**
     * Scope-level processor used by config post-processing must surface the same failure.
     *
     * @return void
     */
    public function testProcessorThrowsWhenSecureBaseUrlIsNull(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            'Cannot resolve "{{secure_base_url}}" because "web/secure/base_url" is empty.'
        );

        /** @var Processor\Placeholder $processor */
        $processor = Bootstrap::getObjectManager()->get(Processor\Placeholder::class);
        $processor->process([
            'default' => [
                'web' => [
                    'unsecure' => [
                        'base_url' => 'http://example.test/',
                    ],
                    'secure' => [
                        'base_url' => null,
                        'base_link_url' => '{{secure_base_url}}',
                    ],
                ],
            ],
        ]);
    }
}
