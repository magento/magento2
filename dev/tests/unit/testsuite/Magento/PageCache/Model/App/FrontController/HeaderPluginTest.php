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
 * @category    Magento
 * @package     Magento_PageCache
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\PageCache\Model\App\FrontController;

class HeaderPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PageCache\Model\App\FrontController\HeaderPlugin
     */
    protected $plugin;

    /**
     * @var \Magento\Core\Model\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\App\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;
    
    /**
     * @var \Magento\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\PageCache\Model\Version|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $versionMock;

    /**
     * @var \Magento\PageCache\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $this->layoutMock = $this->getMock('Magento\Core\Model\Layout', array(), array(), '', false);
        $this->configMock = $this->getMock('Magento\App\ConfigInterface', array(), array(), '', false);
        $this->responseMock = $this->getMock('Magento\App\Response\Http', array(), array(), '', false);
        $this->helperMock = $this->getMock('Magento\PageCache\Helper\Data', array(), array(), '', false);
        $this->versionMock = $this->getMockBuilder('Magento\PageCache\Model\Version')
            ->disableOriginalConstructor()
            ->getMock();
        $this->plugin = new HeaderPlugin($this->layoutMock, $this->configMock, $this->helperMock, $this->versionMock);
    }

    /**
     * Test if layout is not cacheable
     */
    public function testAfterDispatchNotCacheable()
    {
        $pragma = 'no-cache';
        $cacheControl = 'no-store, no-cache, must-revalidate, max-age=0';

        $this->layoutMock->expects($this->once())
            ->method('isCacheable')
            ->will($this->returnValue(false));

        $this->responseMock->expects($this->at(0))
            ->method('setHeader')
            ->with($this->equalTo('pragma'), $this->equalTo($pragma), $this->equalTo(true));
        $this->responseMock->expects($this->at(1))
            ->method('setHeader')
            ->with($this->equalTo('cache-control'), $this->equalTo($cacheControl), $this->equalTo(true));
        $this->responseMock->expects($this->at(2))
            ->method('setHeader')
            ->with($this->equalTo('expires'));

        $this->versionMock->expects($this->once())->method('process');

        $this->plugin->afterDispatch($this->responseMock);
    }

    /**
     * Testing that `cache-control` already exists
     */
    public function testAfterDispatchPrivateCache()
    {
        $pragma = 'cache';

        $this->layoutMock->expects($this->once())
            ->method('isCacheable')
            ->will($this->returnValue(true));

        $this->responseMock->expects($this->at(0))
            ->method('setHeader')
            ->with($this->equalTo('pragma'), $this->equalTo($pragma), $this->equalTo(true));
        $this->responseMock->expects($this->at(1))
            ->method('getHeader')
            ->with($this->equalTo('cache-control'))
            ->will($this->returnValue(true));

        $this->versionMock->expects($this->once())->method('process');

        $this->plugin->afterDispatch($this->responseMock);
    }

    /**
     * Test setting public headers
     */
    public function testAfterDispatchPublicCache()
    {
        $maxAge = 0;
        $pragma = 'cache';
        $cacheControl = 'public, max-age=' . $maxAge;

        $this->layoutMock->expects($this->once())
            ->method('isCacheable')
            ->will($this->returnValue(true));

        $this->helperMock->expects($this->once())->method('getPublicMaxAgeCache')->will($this->returnValue(0));

        $this->responseMock->expects($this->at(0))
            ->method('setHeader')
            ->with($this->equalTo('pragma'), $this->equalTo($pragma), $this->equalTo(true));
        $this->responseMock->expects($this->at(1))
            ->method('getHeader')
            ->with($this->equalTo('cache-control'))
            ->will($this->returnValue(false));
        $this->responseMock->expects($this->at(2))
            ->method('setHeader')
            ->with($this->equalTo('cache-control'), $this->equalTo($cacheControl), $this->equalTo(true));
        $this->responseMock->expects($this->at(3))
            ->method('setHeader')
            ->with($this->equalTo('expires'));

        $this->versionMock->expects($this->once())->method('process');

        $this->plugin->afterDispatch($this->responseMock);
    }
}
