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

namespace Magento\PageCache\Model\App\FrontController;

class VarnishPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VarnishPlugin
     */
    protected $plugin;

    /**
     * @var \Magento\PageCache\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\App\PageCache\Version|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $versionMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stateMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\FrontControllerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $frontControllerMock;

    /**
     * @var \Closure
     */
    protected $closure;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * SetUp
     */
    public function setUp()
    {
        $this->configMock = $this->getMock('Magento\PageCache\Model\Config', array(), array(), '', false);
        $this->versionMock = $this->getMock('Magento\Framework\App\PageCache\Version', array(), array(), '', false);
        $this->stateMock = $this->getMock('Magento\Framework\App\State', array(), array(), '', false);
        $this->frontControllerMock = $this->getMock(
            'Magento\Framework\App\FrontControllerInterface',
            array(),
            array(),
            '',
            false
        );
        $this->requestMock = $this->getMock('Magento\Framework\App\RequestInterface', array(), array(), '', false);
        $this->responseMock = $this->getMock('Magento\Framework\App\Response\Http', array(), array(), '', false);
        $response = $this->responseMock;
        $this->closure = function () use ($response) {
            return $response;
        };
        $this->plugin = new VarnishPlugin(
            $this->configMock,
            $this->versionMock,
            $this->stateMock
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAroundDispatchReturnsCache($state)
    {
        $this->configMock
            ->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->configMock
            ->expects($this->once())
            ->method('getType')
            ->will($this->returnValue(\Magento\PageCache\Model\Config::VARNISH));
        $this->versionMock
            ->expects($this->once())
            ->method('process');
        $this->stateMock->expects($this->any())
            ->method('getMode')
            ->will($this->returnValue($state));
        if ($state == \Magento\Framework\App\State::MODE_DEVELOPER) {
            $this->responseMock->expects($this->once())
                ->method('setHeader')
                ->with('X-Magento-Debug');
        } else {
            $this->responseMock->expects($this->never())
                ->method('setHeader');
        }
        $this->plugin->aroundDispatch($this->frontControllerMock, $this->closure, $this->requestMock);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAroundDispatchDisabled($state)
    {
        $this->configMock
            ->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(null));
        $this->versionMock
            ->expects($this->never())
            ->method('process');
        $this->stateMock->expects($this->any())
            ->method('getMode')
            ->will($this->returnValue($state));
        $this->responseMock->expects($this->never())
            ->method('setHeader');
        $this->plugin->aroundDispatch($this->frontControllerMock, $this->closure, $this->requestMock);
    }

    public function dataProvider()
    {
        return array(
            'developer_mode' => array(\Magento\Framework\App\State::MODE_DEVELOPER),
            'production' => array(\Magento\Framework\App\State::MODE_PRODUCTION),
        );
    }
}
