<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Address;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\ToOrder;
use Magento\Quote\Model\Quote\Address\ToOrderAddress;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\AddressRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests Address convert to order
 */
class ToOrderAddressTest extends TestCase
{
    /**
     * @var Copy|MockObject
     */
    protected $objectCopyMock;

    /**
     * @var AddressRepository|MockObject
     */
    protected $orderAddressRepositoryMock;

    /**
     * @var OrderInterface|MockObject
     */
    protected $orderInterfaceMock;

    /**
     * @var ToOrder
     */
    protected $converter;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    protected function setUp(): void
    {
        $this->orderAddressRepositoryMock = $this->createPartialMock(
            AddressRepository::class,
            ['create']
        );
        $this->objectCopyMock = $this->createMock(Copy::class);
        $this->orderInterfaceMock = $this->getMockForAbstractClass(OrderAddressInterface::class);
        $this->dataObjectHelper = $this->createMock(DataObjectHelper::class);
        $objectManager = new ObjectManager($this);
        $this->converter = $objectManager->getObject(
            ToOrderAddress::class,
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
         * @var Address $object
         */
        $object = $this->createMock(Address::class);
        $this->objectCopyMock->expects($this->once())->method('getDataFromFieldset')->with(
            'sales_convert_quote_address',
            'to_order_address',
            $object
        )->willReturn($orderData);
        $this->dataObjectHelper->expects($this->once())->method('populateWithArray')
            ->with($this->orderInterfaceMock, ['test' => 'beer'], OrderAddressInterface::class)
            ->willReturnSelf();
        $this->orderAddressRepositoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->orderInterfaceMock);
        $this->assertSame($this->orderInterfaceMock, $this->converter->convert($object, $data));
    }
}
