<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\App\FrontController\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Url;
use Magento\Store\App\FrontController\Plugin\RequestPreprocessor;
use Magento\Store\Model\BaseUrlChecker;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RequestPreprocessorTest extends TestCase
{
    /**
     * @var RequestPreprocessor
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var MockObject
     */
    protected $_urlMock;

    /**
     * @var MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var MockObject
     */
    protected $closureMock;

    /**
     * @var MockObject
     */
    protected $_requestMock;

    /**
     * @var MockObject
     */
    protected $_storeMock;

    /**
     * @var MockObject
     */
    protected $subjectMock;

    /**
     * @var BaseUrlChecker|MockObject
     */
    protected $baseUrlChecker;

    /**
     * @var ResponseFactory|MockObject
     */
    protected $responseFactoryMock;

    protected function setUp(): void
    {
        $this->_storeMock = $this->createMock(Store::class);
        $this->_requestMock = $this->createMock(Http::class);
        $this->closureMock = function () {
            return 'Expected';
        };
        $this->_storeManagerMock = $this->createMock(StoreManager::class);
        $this->_urlMock = $this->createMock(Url::class);
        $this->_scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->subjectMock = $this->createMock(FrontController::class);
        $this->responseFactoryMock = $this->createMock(ResponseFactory::class);

        $this->baseUrlChecker = $this->createMock(BaseUrlChecker::class);
        $this->baseUrlChecker->expects($this->any())
            ->method('execute')
            ->willReturn(true);

        $this->_model = new RequestPreprocessor(
            $this->_storeManagerMock,
            $this->_urlMock,
            $this->_scopeConfigMock,
            $this->responseFactoryMock
        );

        $modelProperty = (new \ReflectionClass(get_class($this->_model)))
            ->getProperty('baseUrlChecker');

        $modelProperty->setValue($this->_model, $this->baseUrlChecker);
    }

    public function testAroundDispatchIfRedirectCodeNotExist()
    {
        $this->_requestMock->expects($this->once())->method('setDispatched')->with(false);
        $this->_scopeConfigMock->expects($this->never())->method('getValue')->with('web/url/redirect_to_base');
        $this->_requestMock->expects($this->never())->method('getRequestUri');
        $this->baseUrlChecker->expects($this->any())->method('isEnabled')->willReturn(false);
        $this->assertEquals(
            'Expected',
            $this->_model->aroundDispatch($this->subjectMock, $this->closureMock, $this->_requestMock)
        );
    }

    public function testAroundDispatchIfRedirectCodeExist()
    {
        $this->_requestMock->expects($this->once())->method('setDispatched')->with(false);
        $this->_storeManagerMock->expects(
            $this->any()
        )->method(
            'getStore'
        )->willReturn(
            $this->_storeMock
        );
        $this->_storeMock->expects($this->once())->method('getBaseUrl');
        $this->_requestMock->expects($this->never())->method('getRequestUri');
        $this->baseUrlChecker->expects($this->any())->method('isEnabled')->willReturn(true);
        $this->assertEquals(
            'Expected',
            $this->_model->aroundDispatch($this->subjectMock, $this->closureMock, $this->_requestMock)
        );
    }

    public function testAroundDispatchIfBaseUrlNotExists()
    {
        $this->_requestMock->expects($this->once())->method('setDispatched')->with(false);
        $this->_storeManagerMock->expects(
            $this->any()
        )->method(
            'getStore'
        )->willReturn(
            $this->_storeMock
        );
        $this->_storeMock->expects($this->once())->method('getBaseUrl')->willReturn(false);
        $this->_requestMock->expects($this->never())->method('getRequestUri');
        $this->baseUrlChecker->expects($this->any())->method('isEnabled')->willReturn(true);
        $this->assertEquals(
            'Expected',
            $this->_model->aroundDispatch($this->subjectMock, $this->closureMock, $this->_requestMock)
        );
    }

    public function testRedirectPreservesQueryStringForGetRequests()
    {
        $baseUrlChecker = $this->createMock(BaseUrlChecker::class);
        $baseUrlChecker->expects($this->any())->method('isEnabled')->willReturn(true);
        $baseUrlChecker->expects($this->any())->method('execute')->willReturn(false);
        $modelProperty = (new \ReflectionClass(get_class($this->_model)))
            ->getProperty('baseUrlChecker');
        $modelProperty->setValue($this->_model, $baseUrlChecker);

        $this->_storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->_storeMock);
        $this->_storeMock->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn('https://us.example.com/');
        $this->_storeMock->expects($this->any())
            ->method('isCurrentlySecure')
            ->willReturn(true);

        $this->_requestMock->expects($this->any())->method('isPost')->willReturn(false);
        $this->_requestMock->expects($this->any())
            ->method('getPathInfo')
            ->willReturn('/loginascustomer/login/index/');

        $this->_requestMock->expects($this->any())
            ->method('getQueryValue')
            ->willReturn(['secret' => 'abc123']);

        $this->_urlMock->expects($this->any())
            ->method('getDirectUrl')
            ->with('loginascustomer/login/index/', ['_nosid' => true])
            ->willReturn('https://us.example.com/loginascustomer/login/index/');
        $this->_urlMock->expects($this->any())
            ->method('getRedirectUrl')
            ->with('https://us.example.com/loginascustomer/login/index/?secret=abc123')
            ->willReturn('https://us.example.com/loginascustomer/login/index/?secret=abc123');

        $this->_scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturn(302);

        $responseMock = $this->createMock(HttpResponse::class);
        $responseMock->expects($this->once())
            ->method('setRedirect')
            ->with('https://us.example.com/loginascustomer/login/index/?secret=abc123', 302);
        $responseMock->expects($this->once())->method('setNoCacheHeaders');

        $this->responseFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($responseMock);

        $this->_model->aroundDispatch($this->subjectMock, $this->closureMock, $this->_requestMock);
    }

    public function testRedirectWithoutQueryStringWorksAsExpected()
    {
        $baseUrlChecker = $this->createMock(BaseUrlChecker::class);
        $baseUrlChecker->expects($this->any())->method('isEnabled')->willReturn(true);
        $baseUrlChecker->expects($this->any())->method('execute')->willReturn(false);
        $modelProperty = (new \ReflectionClass(get_class($this->_model)))
            ->getProperty('baseUrlChecker');
        $modelProperty->setValue($this->_model, $baseUrlChecker);

        $this->_storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->_storeMock);
        $this->_storeMock->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn('https://us.example.com/');
        $this->_storeMock->expects($this->any())
            ->method('isCurrentlySecure')
            ->willReturn(true);

        $this->_requestMock->expects($this->any())->method('isPost')->willReturn(false);
        $this->_requestMock->expects($this->any())
            ->method('getPathInfo')
            ->willReturn('/catalog/product/view/');

        $this->_requestMock->expects($this->any())
            ->method('getQueryValue')
            ->willReturn([]);

        $this->_urlMock->expects($this->any())
            ->method('getDirectUrl')
            ->with('catalog/product/view/', ['_nosid' => true])
            ->willReturn('https://us.example.com/catalog/product/view/');
        $this->_urlMock->expects($this->any())
            ->method('getRedirectUrl')
            ->with('https://us.example.com/catalog/product/view/')
            ->willReturn('https://us.example.com/catalog/product/view/');

        $this->_scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturn(302);

        $responseMock = $this->createMock(HttpResponse::class);
        $responseMock->expects($this->once())
            ->method('setRedirect')
            ->with('https://us.example.com/catalog/product/view/', 302);
        $responseMock->expects($this->once())->method('setNoCacheHeaders');

        $this->responseFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($responseMock);

        $this->_model->aroundDispatch($this->subjectMock, $this->closureMock, $this->_requestMock);
    }

    public function testPostRedirectDoesNotAppendQueryString()
    {
        $baseUrlChecker = $this->createMock(BaseUrlChecker::class);
        $baseUrlChecker->expects($this->any())->method('isEnabled')->willReturn(false);
        $baseUrlChecker->expects($this->any())->method('isFrontendSecure')->willReturn(true);
        $baseUrlChecker->expects($this->any())->method('execute')->willReturn(false);
        $modelProperty = (new \ReflectionClass(get_class($this->_model)))
            ->getProperty('baseUrlChecker');
        $modelProperty->setValue($this->_model, $baseUrlChecker);

        $this->_storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->_storeMock);
        $this->_storeMock->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn('https://us.example.com/');
        $this->_storeMock->expects($this->any())
            ->method('isCurrentlySecure')
            ->willReturn(true);

        $this->_requestMock->expects($this->any())->method('isPost')->willReturn(true);
        $this->_requestMock->expects($this->any())->method('isSecure')->willReturn(false);
        $this->_requestMock->expects($this->any())
            ->method('getPathInfo')
            ->willReturn('/checkout/cart/add/');

        $this->_requestMock->expects($this->never())->method('getQueryValue');

        $this->_urlMock->expects($this->any())
            ->method('getDirectUrl')
            ->with('checkout/cart/add/', ['_nosid' => true])
            ->willReturn('https://us.example.com/checkout/cart/add/');
        $this->_urlMock->expects($this->any())
            ->method('getRedirectUrl')
            ->with('https://us.example.com/checkout/cart/add/')
            ->willReturn('https://us.example.com/checkout/cart/add/');

        $this->_scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturn(302);

        $responseMock = $this->createMock(HttpResponse::class);
        $responseMock->expects($this->once())
            ->method('setRedirect')
            ->with('https://us.example.com/checkout/cart/add/', 302);
        $responseMock->expects($this->once())->method('setNoCacheHeaders');

        $this->responseFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($responseMock);

        $this->_model->aroundDispatch($this->subjectMock, $this->closureMock, $this->_requestMock);
    }
}
