<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote\Address;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Tests Address convert to order
 */
class ToOrderAddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DataObject\Copy | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectCopyMock;

    /**
     * @var \Magento\Sales\Model\Order\AddressRepository | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderAddressRepositoryMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderInterfaceMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address\ToOrder
     */
    protected $converter;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    protected function setUp()
    {
        $this->orderAddressRepositoryMock = $this->getMock(
            \Magento\Sales\Model\Order\AddressRepository::class,
            ['create'],
            [],
            '',
            false
        );
        $this->objectCopyMock = $this->getMock(\Magento\Framework\DataObject\Copy::class, [], [], '', false);
        $this->orderInterfaceMock = $this->getMock(
            \Magento\Sales\Api\Data\OrderAddressInterface::class,
            [],
            [],
            '',
            false
        );
        $this->dataObjectHelper = $this->getMock(\Magento\Framework\Api\DataObjectHelper::class, [], [], '', false);
        $objectManager = new ObjectManager($this);
        $this->converter = $objectManager->getObject(
            \Magento\Quote\Model\Quote\Address\ToOrderAddress::class,
            [
                'orderAddressRepository' => $this->orderAddressRepositoryMock,
                'objectCopyService' => $this->objectCopyMock,
                'dataObjectHelper' => $this->dataObjectHelper
            ]
        );
    }

    public function testConvert()
    {
        $orderData = ['test' => 'test1'];
        $data = ['test' => 'beer'];
        /**
         * @var \Magento\Quote\Model\Quote\Address $object
         */
        $object = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);
        $this->objectCopyMock->expects($this->once())->method('getDataFromFieldset')->with(
            'sales_convert_quote_address',
            'to_order_address',
            $object
        )->willReturn($orderData);
        $this->dataObjectHelper->expects($this->once())->method('populateWithArray')
            ->with($this->orderInterfaceMock, ['test' => 'beer'], \Magento\Sales\Api\Data\OrderAddressInterface::class)
            ->willReturnSelf();
        $this->orderAddressRepositoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->orderInterfaceMock);
        $this->assertSame($this->orderInterfaceMock, $this->converter->convert($object, $data));
    }
}
