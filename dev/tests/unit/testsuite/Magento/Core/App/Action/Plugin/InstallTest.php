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
namespace Magento\Core\App\Action\Plugin;

class InstallTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\App\Action\Plugin\Install
     */
    protected $_plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appStateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_response;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_invocationChainMock;

    protected function setUp()
    {
        $this->_appStateMock = $this->getMock('Magento\App\State', array(), array(), '', false);
        $this->_response = $this->getMock('Magento\App\ResponseInterface', array('setRedirect', 'sendResponse'));
        $this->_urlMock = $this->getMock('Magento\Url', array(), array(), '', false);
        $this->_invocationChainMock =
            $this->getMock('Magento\Code\Plugin\InvocationChain', array(), array(), '', false);
        $this->_plugin = new \Magento\Core\App\Action\Plugin\Install(
            $this->_appStateMock,
            $this->_response,
            $this->_urlMock,
            $this->getMock('\Magento\App\ActionFlag', array(), array(), '', false)
        );
    }

    public function testAroundDispatch()
    {
        $url = 'http://example.com';
        $this->_appStateMock->expects($this->once())->method('isInstalled')->will($this->returnValue(false));
        $this->_urlMock->expects($this->once())->method('getUrl')->with('install')->will($this->returnValue($url));
        $this->_response->expects($this->once())->method('setRedirect')->with($url);
        $this->_invocationChainMock->expects($this->never())->method('proceed');
        $this->_plugin->aroundDispatch(array(), $this->_invocationChainMock);
    }

    public function testAroundDispatchWhenApplicationIsInstalled()
    {
        $this->_appStateMock->expects($this->once())->method('isInstalled')->will($this->returnValue(true));

        $this->_invocationChainMock
            ->expects($this->once())
            ->method('proceed')
            ->with(array())
            ->will($this->returnValue('ExpectedValue'));
        $this->_plugin->aroundDispatch(array(), $this->_invocationChainMock);
    }
}