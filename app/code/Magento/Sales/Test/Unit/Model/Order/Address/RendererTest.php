<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Address;

use Magento\Customer\Block\Address\Renderer\RendererInterface as CustomerAddressBlockRenderer;
use Magento\Customer\Model\Address\Config as CustomerAddressConfig;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\Order\Address\Renderer as OrderAddressRenderer;

/**
 * Class RendererTest covers Magento\Sales\Model\Order\Address\Renderer::format.
 */
class RendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Order address renderer instance.
     *
     * @var OrderAddressRenderer
     */
    private $orderAddressRenderer;

    /**
     * Object manager helper instance.
     *
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * Customer address config instance mock.
     *
     * @var CustomerAddressConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerAddressConfigMock;

    /**
     * Event manager instance mock.
     *
     * @var EventManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * Order address instance mock.
     *
     * @var OrderAddress|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderAddressMock;

    /**
     * Order instance mock.
     *
     * @var Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * Customer address block renderer instance mock.
     *
     * @var CustomerAddressBlockRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerAddressBlockRendererMock;

    /**
     * Prepare mocks for tests.
     */
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

    /**
     * Run test format.
     */
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

    /**
     * Run test format without renderer.
     */
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
     * Set expectations for store.
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
