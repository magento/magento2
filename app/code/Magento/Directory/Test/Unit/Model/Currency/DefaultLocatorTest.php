<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Model\Currency;

class DefaultLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Directory\Model\Currency\DefaultLocator
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_configMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_requestMock;

    protected function setUp(): void
    {
        $backendData = $this->createMock(\Magento\Backend\Helper\Data::class);
        $this->_requestMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [$backendData],
            '',
            false,
            false,
            true,
            ['getParam']
        );
        $this->_configMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->_storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $this->_model = new \Magento\Directory\Model\Currency\DefaultLocator(
            $this->_configMock,
            $this->_storeManagerMock
        );
    }

    public function testGetDefaultCurrencyReturnDefaultStoreDefaultCurrencyIfNoStoreIsSpecified()
    {
        $this->_configMock->expects($this->once())->method('getValue')->willReturn('storeCurrency');
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
        )->willReturn(
            'someStore'
        );
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $storeMock->expects($this->once())->method('getBaseCurrencyCode')->willReturn('storeCurrency');
        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getStore'
        )->with(
            'someStore'
        )->willReturn(
            $storeMock
        );
        $this->assertEquals('storeCurrency', $this->_model->getDefaultCurrency($this->_requestMock));
    }

    public function testGetDefaultCurrencyReturnWebsiteDefaultCurrency()
    {
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->willReturnMap(
            [['store', null, ''], ['website', null, 'someWebsite']]
        );
        $websiteMock = $this->createMock(\Magento\Store\Model\Website::class);
        $websiteMock->expects(
            $this->once()
        )->method(
            'getBaseCurrencyCode'
        )->willReturn(
            'websiteCurrency'
        );
        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getWebsite'
        )->with(
            'someWebsite'
        )->willReturn(
            $websiteMock
        );
        $this->assertEquals('websiteCurrency', $this->_model->getDefaultCurrency($this->_requestMock));
    }

    public function testGetDefaultCurrencyReturnGroupDefaultCurrency()
    {
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->willReturnMap(
            
                [['store', null, ''], ['website', null, ''], ['group', null, 'someGroup']]
            
        );
        $websiteMock = $this->createMock(\Magento\Store\Model\Website::class);
        $websiteMock->expects(
            $this->once()
        )->method(
            'getBaseCurrencyCode'
        )->willReturn(
            'websiteCurrency'
        );

        $groupMock = $this->createMock(\Magento\Store\Model\Group::class);
        $groupMock->expects($this->once())->method('getWebsite')->willReturn($websiteMock);

        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getGroup'
        )->with(
            'someGroup'
        )->willReturn(
            $groupMock
        );
        $this->assertEquals('websiteCurrency', $this->_model->getDefaultCurrency($this->_requestMock));
    }
}
