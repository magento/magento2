<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\App\FrontController\Plugin;

class RequestPreprocessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\App\FrontController\Plugin\RequestPreprocessor
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $closureMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \Magento\Store\Model\BaseUrlChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $baseUrlChecker;

    protected function setUp()
    {
        $this->_storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->_requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->closureMock = function () {
            return 'Expected';
        };
        $this->_storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManager::class, [], [], '', false);
        $this->_urlMock = $this->getMock(\Magento\Framework\Url::class, [], [], '', false);
        $this->_scopeConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->subjectMock = $this->getMock(\Magento\Framework\App\FrontController::class, [], [], '', false);

        $this->baseUrlChecker = $this->getMock(\Magento\Store\Model\BaseUrlChecker::class, [], [], '', false);
        $this->baseUrlChecker->expects($this->any())
            ->method('execute')
            ->willReturn(true);

        $this->_model = new \Magento\Store\App\FrontController\Plugin\RequestPreprocessor(
            $this->_storeManagerMock,
            $this->_urlMock,
            $this->_scopeConfigMock,
            $this->getMock(\Magento\Framework\App\ResponseFactory::class, [], [], '', false)
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
        )->will(
            $this->returnValue($this->_storeMock)
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
        )->will(
            $this->returnValue($this->_storeMock)
        );
        $this->_storeMock->expects($this->once())->method('getBaseUrl')->will($this->returnValue(false));
        $this->_requestMock->expects($this->never())->method('getRequestUri');
        $this->baseUrlChecker->expects($this->any())->method('isEnabled')->willReturn(true);
        $this->assertEquals(
            'Expected',
            $this->_model->aroundDispatch($this->subjectMock, $this->closureMock, $this->_requestMock)
        );
    }
}
