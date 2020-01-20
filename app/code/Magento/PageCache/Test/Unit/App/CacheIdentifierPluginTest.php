<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\App;

use Magento\PageCache\Model\Config;
use Magento\Store\Model\StoreManager;

/**
 * Class CacheIdentifierPluginTest
 *
 * Test for plugin to identifier to work with design exceptions
 */
class CacheIdentifierPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\PageCache\Model\App\CacheIdentifierPlugin
     */
    protected $plugin;

    /**
     * @var \Magento\Framework\View\DesignExceptions
     */
    protected $designExceptionsMock;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $requestMock;

    /**
     * @var Config
     */
    protected $pageCacheConfigMock;

    /**
     * Set up data for test
     */
    protected function setUp()
    {
        $this->designExceptionsMock = $this->createPartialMock(
            \Magento\Framework\View\DesignExceptions::class,
            ['getThemeByRequest']
        );
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->pageCacheConfigMock = $this->createPartialMock(
            \Magento\PageCache\Model\Config::class,
            ['getType', 'isEnabled']
        );

        $this->plugin = new \Magento\PageCache\Model\App\CacheIdentifierPlugin(
            $this->designExceptionsMock,
            $this->requestMock,
            $this->pageCacheConfigMock
        );
    }

    /**
     * Test of adding design exceptions + run code to the key of cache hash
     *
     * @param string $cacheType
     * @param bool $isPageCacheEnabled
     * @param string|false $result
     * @param string $uaException
     * @param string $expected
     * @dataProvider afterGetValueDataProvider
     */
    public function testAfterGetValue($cacheType, $isPageCacheEnabled, $result, $uaException, $expected)
    {
        $identifierMock = $this->createMock(\Magento\Framework\App\PageCache\Identifier::class);

        $this->pageCacheConfigMock->expects($this->once())
            ->method('getType')
            ->will($this->returnValue($cacheType));
        $this->pageCacheConfigMock->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue($isPageCacheEnabled));
        $this->designExceptionsMock->expects($this->any())
            ->method('getThemeByRequest')
            ->will($this->returnValue($uaException));

        $this->assertEquals($expected, $this->plugin->afterGetValue($identifierMock, $result));
    }

    /**
     * Data provider for testAfterGetValue
     *
     * @return array
     */
    public function afterGetValueDataProvider()
    {
        return [
            'Varnish + PageCache enabled' => [Config::VARNISH, true, null, false, false],
            'Built-in + PageCache disabled' => [Config::BUILT_IN, false, null, false, false],
            'Built-in + PageCache enabled' => [Config::BUILT_IN, true, null, false, false],
            'Built-in, PageCache enabled, no user-agent exceptions' => [Config::BUILT_IN,
                true,
                'aa123aa',
                false,
                'aa123aa'
            ],
            'Built-in, PageCache enabled, with design exception' => [Config::BUILT_IN,
                true,
                'aa123aa',
                '7',
                'DESIGN=7|aa123aa'
            ]
        ];
    }

    /**
     * Tests that different stores cause different identifiers
     * (property based testing approach)
     */
    public function testAfterGetValueRunParamsCauseDifferentIdentifiers()
    {
        $identifierMock = $this->createMock(\Magento\Framework\App\PageCache\Identifier::class);

        $this->pageCacheConfigMock->expects($this->any())
            ->method('getType')
            ->willReturn(Config::BUILT_IN);
        $this->pageCacheConfigMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true);

        $defaultRequestMock = clone $this->requestMock;
        $defaultRequestMock->expects($this->any())
            ->method('getServerValue')
            ->willReturnCallback(
                function ($param) {
                    if ($param == StoreManager::PARAM_RUN_TYPE) {
                        return 'store';
                    }
                    if ($param == StoreManager::PARAM_RUN_CODE) {
                        return 'default';
                    }
                }
            );

        $nullSha1 = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';

        $defaultPlugin = new \Magento\PageCache\Model\App\CacheIdentifierPlugin(
            $this->designExceptionsMock,
            $defaultRequestMock,
            $this->pageCacheConfigMock
        );

        $defaultStoreResult = $defaultPlugin->afterGetValue($identifierMock, $nullSha1);

        $otherRequestMock = clone $this->requestMock;
        $otherRequestMock->expects($this->any())
            ->method('getServerValue')
            ->willReturnCallback(
                function ($param) {
                    if ($param == StoreManager::PARAM_RUN_TYPE) {
                        return 'store';
                    }
                    if ($param == StoreManager::PARAM_RUN_CODE) {
                        return 'klingon';
                    }
                }
            );

        $otherPlugin = new \Magento\PageCache\Model\App\CacheIdentifierPlugin(
            $this->designExceptionsMock,
            $otherRequestMock,
            $this->pageCacheConfigMock
        );
        $otherStoreResult = $otherPlugin->afterGetValue($identifierMock, $nullSha1);

        $this->assertNotEquals($nullSha1, $defaultStoreResult);
        $this->assertNotEquals($nullSha1, $otherStoreResult);
        $this->assertNotEquals($defaultStoreResult, $otherStoreResult);
    }
}
