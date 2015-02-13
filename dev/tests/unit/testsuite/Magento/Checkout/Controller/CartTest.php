<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller;

class CartTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Framework\App\Request\Http | \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http | \PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $responseRedirectMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect | \PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

    /**
     * @var \Magento\Checkout\Model\Session | \PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutModelSessionMock;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var \Magento\Store\Model\Store | \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\Framework\UrlInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $contextUrlMock;

    /**
     * @var \Magento\Checkout\Controller\Cart
     */
    private $controller;

    protected function setUp()
    {
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseRedirectMock = $this->getMockBuilder('Magento\Framework\App\Response\RedirectInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->checkoutModelSessionMock = $this->getMockBuilder('Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods(['setContinueShoppingUrl'])
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->getMock();

        $this->storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextUrlMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = (new \Magento\TestFramework\Helper\ObjectManager($this))->getObject(
            'Magento\Checkout\Controller\Cart',
            [
                'response'              => $this->responseMock,
                'request'               => $this->requestMock,
                'checkoutSession'       => $this->checkoutModelSessionMock,
                'scopeConfig'           => $this->scopeConfigMock,
                'redirect'              => $this->responseRedirectMock,
                'storeManager'          => $this->storeManagerMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
                'url'                   => $this->contextUrlMock
            ]
        );
    }

    public function testControllerImplementsProductViewInterface()
    {
        $this->assertInstanceOf('Magento\Catalog\Controller\Product\View\ViewInterface', $this->controller);
    }

    public function testGoBackWithBackUrlInArgs()
    {
        $backUrl = 'test';

        $this->resultRedirectMock->expects($this->once())
            ->method('setUrl')
            ->with($backUrl)
            ->willReturnSelf();

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $reflectionObject = new \ReflectionObject($this->controller);
        $reflectionMethod = $reflectionObject->getMethod('_goBack');
        $reflectionMethod->setAccessible(true);

        $this->assertSame(
            $this->resultRedirectMock,
            $reflectionMethod->invokeArgs(
                $this->controller, [$backUrl]
            )
        );
    }

    public function testGoBackWithNoBackUrlAndShouldNotRedirectToCart()
    {
        $refererUrl = 'http://some-url/index.php/product.html';

        $this->responseRedirectMock->expects($this->once())
            ->method('getRefererUrl')
            ->willreturn($refererUrl);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('checkout/cart/redirect_to_cart')
            ->willreturn(0);

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('return_url')
            ->willreturn('http://malicious.com/');

        $this->storeMock->expects($this->exactly(2))
            ->method('getBaseUrl')
            ->willreturn('http://some-url/');

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willreturn($this->storeMock);

        $this->resultRedirectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->resultRedirectMock->expects($this->once())
            ->method('setUrl')
            ->with($refererUrl)
            ->willReturnSelf();

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $reflectionObject = new \ReflectionObject($this->controller);
        $reflectionMethod = $reflectionObject->getMethod('_goBack');
        $reflectionMethod->setAccessible(true);

        $this->assertSame($this->resultRedirectMock, $reflectionMethod->invoke($this->controller));
    }

    public function testGoBackWithNoBackUrlAndShouldRedirectToCart()
    {
        $refererUrl = 'http://some-url/index.php/product.html';

        $this->responseRedirectMock->expects($this->exactly(2))
            ->method('getRefererUrl')
            ->willreturn($refererUrl);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('checkout/cart/redirect_to_cart')
            ->willreturn('1');

        $this->requestMock->expects($this->any())
            ->method('getActionName')
            ->willreturn('add');

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('return_url')
            ->willreturn('http://malicious.com/');

        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->willreturn(false);

        $this->storeMock->expects($this->exactly(2))
            ->method('getBaseUrl')
            ->willreturn('http://some-url/');

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willreturn($this->storeMock);

        $this->checkoutModelSessionMock->expects($this->once())
            ->method('setContinueShoppingUrl')
            ->with($refererUrl)
            ->will($this->returnSelf());

        $this->resultRedirectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->contextUrlMock->expects($this->once())
            ->method('getUrl')
            ->with('checkout/cart')->willReturn('checkout/cart');

        $this->resultRedirectMock->expects($this->once())
            ->method('setUrl')
            ->with('checkout/cart')
            ->willReturnSelf();

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $reflectionObject = new \ReflectionObject($this->controller);
        $reflectionMethod = $reflectionObject->getMethod('_goBack');
        $reflectionMethod->setAccessible(true);

        $this->assertSame($this->resultRedirectMock, $reflectionMethod->invoke($this->controller));
    }
}
