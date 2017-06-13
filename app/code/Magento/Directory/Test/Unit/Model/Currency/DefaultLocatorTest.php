<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Model\Currency;

class DefaultLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Model\Currency\DefaultLocator
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    protected function setUp()
    {
        $backendData = $this->getMock(\Magento\Backend\Helper\Data::class, [], [], '', false);
        $this->_requestMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [$backendData],
            '',
            false,
            false,
            true,
            ['getParam']
        );
        $this->_configMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->_storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManager::class, [], [], '', false);
        $this->_model = new \Magento\Directory\Model\Currency\DefaultLocator(
            $this->_configMock,
            $this->_storeManagerMock
        );
    }

    public function testGetDefaultCurrencyReturnDefaultStoreDefaultCurrencyIfNoStoreIsSpecified()
    {
        $this->_configMock->expects($this->once())->method('getValue')->will($this->returnValue('storeCurrency'));
        $this->assertEquals('storeCurrency', $this->_model->getDefaultCurrency($this->_requestMock));
    }

    public function testGetDefaultCurrencyReturnStoreDefaultCurrency()
    {
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->with(
            'store'
        )->will(
            $this->returnValue('someStore')
        );
        $storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $storeMock->expects($this->once())->method('getBaseCurrencyCode')->will($this->returnValue('storeCurrency'));
        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getStore'
        )->with(
            'someStore'
        )->will(
            $this->returnValue($storeMock)
        );
        $this->assertEquals('storeCurrency', $this->_model->getDefaultCurrency($this->_requestMock));
    }

    public function testGetDefaultCurrencyReturnWebsiteDefaultCurrency()
    {
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->will(
            $this->returnValueMap([['store', null, ''], ['website', null, 'someWebsite']])
        );
        $websiteMock = $this->getMock(\Magento\Store\Model\Website::class, [], [], '', false);
        $websiteMock->expects(
            $this->once()
        )->method(
            'getBaseCurrencyCode'
        )->will(
            $this->returnValue('websiteCurrency')
        );
        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getWebsite'
        )->with(
            'someWebsite'
        )->will(
            $this->returnValue($websiteMock)
        );
        $this->assertEquals('websiteCurrency', $this->_model->getDefaultCurrency($this->_requestMock));
    }

    public function testGetDefaultCurrencyReturnGroupDefaultCurrency()
    {
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->will(
            $this->returnValueMap(
                [['store', null, ''], ['website', null, ''], ['group', null, 'someGroup']]
            )
        );
        $websiteMock = $this->getMock(\Magento\Store\Model\Website::class, [], [], '', false);
        $websiteMock->expects(
            $this->once()
        )->method(
            'getBaseCurrencyCode'
        )->will(
            $this->returnValue('websiteCurrency')
        );

        $groupMock = $this->getMock(\Magento\Store\Model\Group::class, [], [], '', false);
        $groupMock->expects($this->once())->method('getWebsite')->will($this->returnValue($websiteMock));

        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getGroup'
        )->with(
            'someGroup'
        )->will(
            $this->returnValue($groupMock)
        );
        $this->assertEquals('websiteCurrency', $this->_model->getDefaultCurrency($this->_requestMock));
    }
}
