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

namespace Magento\PageCache\Model\Layout;

class LayoutPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PageCache\Model\Layout\LayoutPlugin
     */
    protected $model;

    /**
     * @var \Magento\App\ResponseInterface
     */
    protected $responseMock;

    /**
     * @var \Magento\Core\Model\Layout
     */
    protected $layoutMock;

    /**
     * @var \Magento\App\ConfigInterface
     */
    protected $configMock;

    public function setUp()
    {
        $this->layoutMock = $this->getMockForAbstractClass(
            'Magento\Core\Model\Layout',
            [],
            '',
            false,
            true,
            true,
            ['isCacheable']
        );
        $this->responseMock = $this->getMock(
            '\Magento\App\Response\Http',
            [],
            [],
            '',
            false
        );
        $this->configMock = $this->getMockForAbstractClass(
            'Magento\App\ConfigInterface',
            [],
            '',
            false,
            true,
            true,
            ['isSetFlag', 'getValue']
        );

        $this->model = new \Magento\PageCache\Model\Layout\LayoutPlugin(
            $this->layoutMock,
            $this->responseMock,
            $this->configMock
        );
    }

    /**
     * @param $layoutIsCacheable
     * @dataProvider afterGenerateXmlDataProvider
     */
    public function testAfterGenerateXml($layoutIsCacheable)
    {
        $maxAge = 180;
        $result = 'test';

        $this->layoutMock->expects($this->once())
            ->method('isCacheable')
            ->will($this->returnValue($layoutIsCacheable));
        if ($layoutIsCacheable) {
            $this->configMock->expects($this->once())
                ->method('getValue')
                ->with(\Magento\PageCache\Model\Config::XML_PAGECACHE_TTL)
                ->will($this->returnValue($maxAge));
            $this->responseMock->expects($this->once())
                ->method('setPublicHeaders')
                ->with($maxAge);
        } else {
            $this->responseMock->expects($this->never())
                ->method('setPublicHeaders');
        }
        $output = $this->model->afterGenerateXml($this->layoutMock, $result);
        $this->assertSame($result, $output);

    }

    public function afterGenerateXmlDataProvider()
    {
        return [
            'Layout is cache-able' => [true],
            'Layout is not cache-able' => [false]
        ];
    }

    /**
     * @param bool $layoutIsCacheable
     * @dataProvider afterGetOutputDataProvider
     */
    public function testAfterGetOutput($layoutIsCacheable)
    {
        $html = 'html';

        $this->layoutMock->expects($this->once())
            ->method('isCacheable')
            ->will($this->returnValue($layoutIsCacheable));
        if ($layoutIsCacheable) {
            $this->responseMock->expects($this->once())
                ->method('setHeader')
                ->with('X-Magento-Tags');
        } else {
            $this->responseMock->expects($this->never())
                ->method('setHeader');
        }
        $output = $this->model->afterGetOutput($this->layoutMock, $html);
        $this->assertSame($output, $html);
    }

    public function afterGetOutputDataProvider()
    {
        return [
            'Layout is cache-able' => [true],
            'Layout is not cache-able' => [false]
        ];
    }
} 