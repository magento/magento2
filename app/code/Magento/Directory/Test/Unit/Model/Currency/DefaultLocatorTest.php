<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model\Currency;

use Magento\Backend\Helper\Data;
use Magento\Directory\Model\Currency\DefaultLocator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DefaultLocatorTest extends TestCase
{
    /**
     * @var DefaultLocator
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_configMock;

    /**
     * @var MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var MockObject
     */
    protected $_requestMock;

    protected function setUp(): void
    {
        $backendData = $this->createMock(Data::class);
        $this->_requestMock = $this->getMockForAbstractClass(
            RequestInterface::class,
            [$backendData],
            '',
            false,
            false,
            true,
            ['getParam']
        );
        $this->_configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->_storeManagerMock = $this->createMock(StoreManager::class);
        $this->_model = new DefaultLocator(
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
        $storeMock = $this->createMock(Store::class);
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
        $websiteMock = $this->createMock(Website::class);
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
        $websiteMock = $this->createMock(Website::class);
        $websiteMock->expects(
            $this->once()
        )->method(
            'getBaseCurrencyCode'
        )->willReturn(
            'websiteCurrency'
        );

        $groupMock = $this->createMock(Group::class);
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
