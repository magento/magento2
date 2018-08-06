<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\App;

use Magento\PageCache\Model\Config;

/**
 * Class CacheIdentifierPluginTest
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
     * Test of adding design exceptions to the kay of cache hash
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
            'Built-in, PageCache enabled, no user-agent exceptions' =>
                [Config::BUILT_IN, true, 'aa123aa', false, 'aa123aa'],
            'Built-in, PageCache enabled, with design exception' => [Config::BUILT_IN, true, 'aa123aa', '7', '7aa123aa']
        ];
    }
}
