<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\PageCache\App;

/**
 * Class CacheIdentifierPluginTest
 * Test for plugin to identifier to work with design exceptions
 */
class CacheIdentifierPluginTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\PageCache\Model\Config
     */
    protected $pageCacheConfigMock;

    /**
     * Set up data for test
     */
    public function setUp()
    {
        $this->designExceptionsMock = $this->getMock(
            'Magento\Framework\View\DesignExceptions',
            ['getThemeByRequest'],
            [],
            '',
            false
        );
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->pageCacheConfigMock = $this->getMock(
            'Magento\PageCache\Model\Config',
            ['getType', 'isEnabled'],
            [],
            '',
            false
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
     * @dataProvider testAfterGetValueDataProvider
     */
    public function testAfterGetValue($cacheType, $isPageCacheEnabled, $result, $uaException, $expected)
    {
        $identifierMock = $this->getMock('Magento\Framework\App\PageCache\Identifier', [], [], '', false);

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
    public function testAfterGetValueDataProvider()
    {
        return [
            'Varnish + PageCache enabled' => [\Magento\PageCache\Model\Config::VARNISH, true, null, false, false],
            'Built-in + PageCache disabled' => [\Magento\PageCache\Model\Config::BUILT_IN, false, null, false, false],
            'Built-in + PageCache enabled' => [\Magento\PageCache\Model\Config::BUILT_IN, true, null, false, false],
            'Built-in, PageCache enabled, no user-agent exceptions' =>
                [\Magento\PageCache\Model\Config::BUILT_IN, true, 'aa123aa', false, 'aa123aa'],
            'Built-in, PageCache enabled, with design exception' =>
                [\Magento\PageCache\Model\Config::BUILT_IN, true, 'aa123aa', '7', '7aa123aa']
        ];
    }
}
