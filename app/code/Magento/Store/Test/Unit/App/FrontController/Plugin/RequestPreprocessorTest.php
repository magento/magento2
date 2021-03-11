<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\App\FrontController\Plugin;

class RequestPreprocessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\App\FrontController\Plugin\RequestPreprocessor
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_urlMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $closureMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    /**
     * @var \Magento\Store\Model\BaseUrlChecker|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $baseUrlChecker;

    protected function setUp(): void
    {
        $this->_storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->_requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->closureMock = function () {
            return 'Expected';
        };
        $this->_storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $this->_urlMock = $this->createMock(\Magento\Framework\Url::class);
        $this->_scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->subjectMock = $this->createMock(\Magento\Framework\App\FrontController::class);

        $this->baseUrlChecker = $this->createMock(\Magento\Store\Model\BaseUrlChecker::class);
        $this->baseUrlChecker->expects($this->any())
            ->method('execute')
            ->willReturn(true);

        $this->_model = new \Magento\Store\App\FrontController\Plugin\RequestPreprocessor(
            $this->_storeManagerMock,
            $this->_urlMock,
            $this->_scopeConfigMock,
            $this->createMock(\Magento\Framework\App\ResponseFactory::class)
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
