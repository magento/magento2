<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\App\FrontController\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\Request\Http;
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

    protected function setUp(): void
    {
        $this->_storeMock = $this->createMock(Store::class);
        $this->_requestMock = $this->createMock(Http::class);
        $this->closureMock = function () {
            return 'Expected';
        };
        $this->_storeManagerMock = $this->createMock(StoreManager::class);
        $this->_urlMock = $this->createMock(Url::class);
        $this->_scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->subjectMock = $this->createMock(FrontController::class);

        $this->baseUrlChecker = $this->createMock(BaseUrlChecker::class);
        $this->baseUrlChecker->expects($this->any())
            ->method('execute')
            ->willReturn(true);

        $this->_model = new RequestPreprocessor(
            $this->_storeManagerMock,
            $this->_urlMock,
            $this->_scopeConfigMock,
            $this->createMock(ResponseFactory::class)
        );

        $modelProperty = (new \ReflectionClass(get_class($this->_model)))
            ->getProperty('baseUrlChecker');

        $modelProperty->setAccessible(true);
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
}
