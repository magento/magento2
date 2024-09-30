<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Address;

use Magento\Customer\Block\Address\Renderer\RendererInterface as CustomerAddressBlockRenderer;
use Magento\Customer\Model\Address\Config as CustomerAddressConfig;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\Order\Address\Renderer as OrderAddressRenderer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Sales\Model\Order\Address\Renderer.
 */
class RendererTest extends TestCase
{
    /**
     * @var OrderAddressRenderer
     */
    private $orderAddressRenderer;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var CustomerAddressConfig|MockObject
     */
    private $customerAddressConfigMock;

    /**
     * @var EventManager|MockObject
     */
    private $eventManagerMock;

    /**
     * @var OrderAddress|MockObject
     */
    private $orderAddressMock;

    /**
     * @var Order|MockObject
     */
    private $orderMock;

    /**
     * @var CustomerAddressBlockRenderer|MockObject
     */
    private $customerAddressBlockRendererMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $storeConfigMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMck;

    /**
     * @ingeritdoc
     */
    protected function setUp(): void
    {
        $this->customerAddressConfigMock = $this->getMockBuilder(CustomerAddressConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(EventManager::class)
            ->getMockForAbstractClass();
        $this->orderAddressMock = $this->getMockBuilder(OrderAddress::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerAddressBlockRendererMock = $this->getMockBuilder(CustomerAddressBlockRenderer::class)
            ->getMockForAbstractClass();

        $this->orderAddressMock->expects(static::any())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->storeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->storeManagerMck = $this->getMockBuilder(StoreManagerInterface::class)
            ->onlyMethods(['setCurrentStore', 'getStore'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->orderAddressRenderer = $this->objectManagerHelper->getObject(
            OrderAddressRenderer::class,
            [
                'addressConfig' => $this->customerAddressConfigMock,
                'eventManager' => $this->eventManagerMock,
                'scopeConfig' => $this->storeConfigMock,
                'storeManager' => $this->storeManagerMck
            ]
        );
    }

    public function testFormat(): void
    {
        $type = 'html';
        $formatType = new DataObject(['renderer' => $this->customerAddressBlockRendererMock]);
        $addressData = ['address', 'data', 'locale' => 1];
        $result = 'result string';

        $this->setStoreExpectations();
        $this->customerAddressConfigMock->expects(static::atLeastOnce())
            ->method('getFormatByCode')
            ->with($type)
            ->willReturn($formatType);
        $this->eventManagerMock->expects(static::once())
            ->method('dispatch')
            ->with('customer_address_format', ['type' => $formatType, 'address' => $this->orderAddressMock]);
        $this->orderAddressMock->expects(static::atLeastOnce())
            ->method('getData')
            ->willReturn($addressData);
        $this->storeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn('1');
        $this->customerAddressBlockRendererMock->expects(static::once())
            ->method('renderArray')
            ->with($addressData, null)
            ->willReturn($result);

        $this->assertEquals($result, $this->orderAddressRenderer->format($this->orderAddressMock, $type));
    }

    public function testFormatNoRenderer(): void
    {
        $type = 'html';

        $this->setStoreExpectations();
        $this->customerAddressConfigMock->expects(static::atLeastOnce())
            ->method('getFormatByCode')
            ->with($type)
            ->willReturn(null);
        $this->eventManagerMock->expects(static::never())
            ->method('dispatch');

        $this->assertNull($this->orderAddressRenderer->format($this->orderAddressMock, $type));
    }

    /**
     * Set expectations for store
     *
     * @return void
     */
    private function setStoreExpectations(): void
    {
        $originalStoreMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();

        $this->orderMock->expects(self::once())->method('getStore')->willReturn($storeMock);

        // One call to setup the store from the order, and an other one to rollback to the original store value
        $expected = [$storeMock, $originalStoreMock];
        $matcher = $this->exactly(count($expected));
        $this->storeManagerMck->expects(self::once())->method('getStore')->willReturn($originalStoreMock);
        $this->storeManagerMck->expects(self::any())->method('setCurrentStore')->with(
            $this->callback(function ($store) use ($matcher, $expected) {
                $this->assertEquals($store, $expected[$matcher->numberOfInvocations()]);
                return true;
            })
        );

        // One call to setup the store from the order, and an other one to rollback to the original store value
        $this->customerAddressConfigMock->expects(self::any())->method('setStore')->with(
            $this->callback(function ($store) use ($matcher, $expected) {
                $this->assertEquals($store, $expected[$matcher->numberOfInvocations()]);
                return true;
            })
        );
    }
}
