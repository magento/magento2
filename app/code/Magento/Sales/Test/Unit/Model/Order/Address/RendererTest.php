<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Address;

use Magento\Sales\Model\Order\Address\Renderer as OrderAddressRenderer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Customer\Model\Address\Config as CustomerAddressConfig;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\Order;
use Magento\Customer\Block\Address\Renderer\RendererInterface as CustomerAddressBlockRenderer;
use Magento\Framework\DataObject;

class RendererTest extends \PHPUnit_Framework_TestCase
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
     * @var CustomerAddressConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerAddressConfigMock;

    /**
     * @var EventManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * @var OrderAddress|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderAddressMock;

    /**
     * @var Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var CustomerAddressBlockRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerAddressBlockRendererMock;

    protected function setUp()
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

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->orderAddressRenderer = $this->objectManagerHelper->getObject(
            OrderAddressRenderer::class,
            [
                'addressConfig' => $this->customerAddressConfigMock,
                'eventManager' => $this->eventManagerMock
            ]
        );
    }

    public function testFormat()
    {
        $type = 'html';
        $formatType = new DataObject(['renderer' => $this->customerAddressBlockRendererMock]);
        $addressData = ['address', 'data'];
        $result = 'result string';

        $this->setStoreExpectations(1);
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
        $this->customerAddressBlockRendererMock->expects(static::once())
            ->method('renderArray')
            ->with($addressData, null)
            ->willReturn($result);

        $this->assertEquals($result, $this->orderAddressRenderer->format($this->orderAddressMock, $type));
    }

    public function testFormatNoRenderer()
    {
        $type = 'html';

        $this->setStoreExpectations(1);
        $this->customerAddressConfigMock->expects(static::atLeastOnce())
            ->method('getFormatByCode')
            ->with($type)
            ->willReturn(null);
        $this->eventManagerMock->expects(static::never())
            ->method('dispatch');

        $this->assertEquals(null, $this->orderAddressRenderer->format($this->orderAddressMock, $type));
    }

    /**
     * Set expectations for store
     *
     * @param string|int $storeId
     * @return void
     */
    private function setStoreExpectations($storeId)
    {
        $this->orderMock->expects(static::atLeastOnce())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->customerAddressConfigMock->expects(static::atLeastOnce())
            ->method('setStore')
            ->with($storeId)
            ->willReturnSelf();
    }
}
