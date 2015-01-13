<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller;

class CartTest extends \PHPUnit_Framework_TestCase
{
    public function testControllerImplementsProductViewInterface()
    {
        $this->assertInstanceOf(
            'Magento\Catalog\Controller\Product\View\ViewInterface',
            $this->getMock('Magento\Checkout\Controller\Cart', [], [], '', false)
        );
    }

    public function testGoBack()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');

        $responseMock = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $responseMock->headersSentThrowsException = false;

        $requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
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
            ['setContinueShoppingUrl'],
            [],
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

        $storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
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
        $arguments = [
            'response' => $responseMock,
            'request' => $requestMock,
            'checkoutSession' => $checkoutSessionMock,
            'scopeConfig' => $configMock,
            'redirect' => $redirect,
            'storeManager' => $storeManagerMock,
        ];

        $controller = $helper->getObject('Magento\Checkout\Controller\Cart', $arguments);

        $reflectionObject = new \ReflectionObject($controller);
        $reflectionMethod = $reflectionObject->getMethod('_goBack');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($controller);
    }
}
