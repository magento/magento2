<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Model\Theme\StoreUserAgentThemeResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test store associated themes in user-agent rules resolver.
 */
class StoreUserAgentThemeResolverTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;
    /**
     * @var Json
     */
    private $serializer;
    /**
     * @var StoreUserAgentThemeResolver
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->serializer = new Json();
        $this->model = new StoreUserAgentThemeResolver(
            $this->scopeConfig,
            $this->serializer
        );
    }

    /**
     * Test that method returns user-agent rules associated themes.
     *
     * @param array|null $config
     * @param array $expected
     * @dataProvider getThemesDataProvider
     */
    public function testGetThemes(?array $config, array $expected)
    {
        $store = $this->getMockForAbstractClass(StoreInterface::class);
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('design/theme/ua_regexp', ScopeInterface::SCOPE_STORE, $store)
            ->willReturn($config !== null ? $this->serializer->serialize($config) : $config);
        $this->assertEquals($expected, $this->model->getThemes($store));
    }

    /**
     * @return array
     */
    public function getThemesDataProvider(): array
    {
        return [
            [
                null,
                []
            ],
            [
                [],
                []
            ],
            [
                [
                    [
                        'search' => '\/Chrome\/i',
                        'regexp' => '\/Chrome\/i',
                        'value' => '1',
                    ],
                ],
                ['1']
            ],
            [
                [
                    [
                        'search' => '\/Chrome\/i',
                        'regexp' => '\/Chrome\/i',
                        'value' => '1',
                    ],
                    [
                        'search' => '\/mozila\/i',
                        'regexp' => '\/mozila\/i',
                        'value' => '2',
                    ],
                ],
                ['1', '2']
            ]
        ];
    }
}
