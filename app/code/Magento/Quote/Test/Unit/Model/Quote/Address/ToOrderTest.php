<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote\Address;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Tests Address convert to order address
 */
class ToOrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DataObject\Copy | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectCopyMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterfaceFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderDataFactoryMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address\ToOrder
     */
    protected $converter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    protected function setUp()
    {
        $this->orderDataFactoryMock = $this->createPartialMock(
            \Magento\Sales\Api\Data\OrderInterfaceFactory::class,
            ['create']
        );
        $this->objectCopyMock = $this->createMock(\Magento\Framework\DataObject\Copy::class);
        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->dataObjectHelper = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);
        $objectManager = new ObjectManager($this);
        $this->converter = $objectManager->getObject(
            \Magento\Quote\Model\Quote\Address\ToOrder::class,
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

        $object = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $object->expects($this->exactly(5))->method('getQuote')->willReturn($quote);
        $quote->expects($this->once())->method('getId')->willReturn($quoteId);
        $quote->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $this->objectCopyMock->expects($this->once())->method('getDataFromFieldset')->with(
            'sales_convert_quote_address',
            'to_order',
            $object
        )->willReturn($orderData);
        $this->dataObjectHelper->expects($this->once())->method('populateWithArray')
            ->with($this->orderMock, ['test' => 'beer'], \Magento\Sales\Api\Data\OrderInterface::class)
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
