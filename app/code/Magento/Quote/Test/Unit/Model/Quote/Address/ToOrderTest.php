<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Address;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\ToOrder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests Address convert to order address
 */
class ToOrderTest extends TestCase
{
    /**
     * @var Copy|MockObject
     */
    protected $objectCopyMock;

    /**
     * @var OrderInterfaceFactory|MockObject
     */
    protected $orderDataFactoryMock;

    /**
     * @var OrderInterface|MockObject
     */
    protected $orderMock;

    /**
     * @var ToOrder
     */
    protected $converter;

    /**
     * @var MockObject
     */
    protected $eventManagerMock;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    protected function setUp(): void
    {
        $this->orderDataFactoryMock = $this->createPartialMock(
            OrderInterfaceFactory::class,
            ['create']
        );
        $this->objectCopyMock = $this->createMock(Copy::class);
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->dataObjectHelper = $this->createMock(DataObjectHelper::class);
        $objectManager = new ObjectManager($this);
        $this->converter = $objectManager->getObject(
            ToOrder::class,
            [
                'orderFactory' => $this->orderDataFactoryMock,
                'objectCopyService' => $this->objectCopyMock,
                'eventManager' => $this->eventManagerMock,
                'dataObjectHelper' => $this->dataObjectHelper
            ]
        );
    }

    public function testConvert()
    {
        $orderData = ['test' => 'test1'];
        $data = ['test' => 'beer'];
        $quoteId = 1;
        $storeId = 777;

        $object = $this->createMock(Address::class);
        $quote = $this->createMock(Quote::class);
        $object->expects($this->exactly(5))->method('getQuote')->willReturn($quote);
        $quote->expects($this->once())->method('getId')->willReturn($quoteId);
        $quote->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $this->objectCopyMock->expects($this->once())->method('getDataFromFieldset')->with(
            'sales_convert_quote_address',
            'to_order',
            $object
        )->willReturn($orderData);
        $this->dataObjectHelper->expects($this->once())->method('populateWithArray')
            ->with($this->orderMock, ['test' => 'beer'], OrderInterface::class)
            ->willReturnSelf();
        $this->orderMock->expects($this->once())->method('setStoreId')->with($storeId)->willReturnSelf();
        $this->orderMock->expects($this->once())->method('setQuoteId')->with($quoteId)->willReturnSelf();
        $this->orderDataFactoryMock->expects($this->once())->method('create')->willReturn($this->orderMock);
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('sales_convert_quote_to_order', ['order' => $this->orderMock, 'quote' => $quote]);
        $this->assertSame($this->orderMock, $this->converter->convert($object, $data));
    }
}
