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
namespace Magento\Checkout\Controller;

class CartTest extends \PHPUnit_Framework_TestCase
{
    public function testControllerImplementsProductViewInterface()
    {
        $this->assertInstanceOf(
            'Magento\Catalog\Controller\Product\View\ViewInterface',
            $this->getMock('Magento\Checkout\Controller\Cart', array(), array(), '', false)
        );
    }

    public function testGoBack()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $storeManagerMock = $this->getMock('Magento\Framework\StoreManagerInterface');

        $responseMock = $this->getMock('Magento\Framework\App\Response\Http', array(), array(), '', false);
        $responseMock->headersSentThrowsException = false;

        $requestMock = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);
        $requestMock->expects($this->any())->method('getActionName')->will($this->returnValue('add'));
        $requestMock->expects(
            $this->at(0)
        )->method(
            'getParam'
        )->with(
            'return_url'
        )->will(
            $this->returnValue('http://malicious.com/')
        );
        $requestMock->expects($this->any())->method('getParam')->will($this->returnValue(null));
        $redirect = $this->getMock('Magento\Framework\App\Response\RedirectInterface');
        $redirect->expects(
            $this->any()
        )->method(
            'getRefererUrl'
        )->will(
            $this->returnValue('http://some-url/index.php/product.html')
        );

        $checkoutSessionMock = $this->getMock(
            'Magento\Checkout\Model\Session',
            array('setContinueShoppingUrl'),
            array(),
            '',
            false
        );
        $checkoutSessionMock->expects(
            $this->once()
        )->method(
            'setContinueShoppingUrl'
        )->with(
            'http://some-url/index.php/product.html'
        )->will(
            $this->returnSelf()
        );

        $redirect->expects(
            $this->once()
        )->method(
            'redirect'
        )->will(
            $this->returnValue('http://some-url/index.php/checkout/cart/')
        );

        $storeMock = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $storeMock->expects($this->any())->method('getBaseUrl')->will($this->returnValue('http://some-url/'));

        $configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            'checkout/cart/redirect_to_cart'
        )->will(
            $this->returnValue('1')
        );
        $storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));
        $arguments = array(
            'response' => $responseMock,
            'request' => $requestMock,
            'checkoutSession' => $checkoutSessionMock,
            'scopeConfig' => $configMock,
            'redirect' => $redirect,
            'storeManager' => $storeManagerMock
        );

        $controller = $helper->getObject('Magento\Checkout\Controller\Cart', $arguments);

        $reflectionObject = new \ReflectionObject($controller);
        $reflectionMethod = $reflectionObject->getMethod('_goBack');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($controller);
    }
}
