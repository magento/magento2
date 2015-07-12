<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote\Address;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Tests Address convert to order address
 */
class ToOrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Object\Copy | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectCopyMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterfaceFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderDataFactoryMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderInterfaceMock;

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
        $this->orderDataFactoryMock = $this->getMock(
            'Magento\Sales\Api\Data\OrderInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->objectCopyMock = $this->getMock('Magento\Framework\Object\Copy', [], [], '', false);
        $this->orderInterfaceMock = $this->getMockForAbstractClass(
            'Magento\Sales\Api\Data\OrderInterface',
            [],
            '',
            false,
            true,
            true,
            ['setStoreId', 'setQuoteId']
        );
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $this->dataObjectHelper = $this->getMock('\Magento\Framework\Api\DataObjectHelper', [], [], '', false);
        $objectManager = new ObjectManager($this);
        $this->converter = $objectManager->getObject(
            'Magento\Quote\Model\Quote\Address\ToOrder',
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

        $object = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false);
        $quote = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $object->expects($this->exactly(5))->method('getQuote')->willReturn($quote);
        $quote->expects($this->once())->method('getId')->willReturn($quoteId);
        $quote->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $this->objectCopyMock->expects($this->once())->method('getDataFromFieldset')->with(
            'quote_convert_address',
            'to_order',
            $object
        )->willReturn($orderData);
        $this->dataObjectHelper->expects($this->once())->method('populateWithArray')
            ->with($this->orderInterfaceMock, ['test' => 'beer'], '\Magento\Sales\Api\Data\OrderInterface')
            ->willReturnSelf();
        $this->orderInterfaceMock->expects($this->once())->method('setStoreId')->with($storeId)->willReturnSelf();
        $this->orderInterfaceMock->expects($this->once())->method('setQuoteId')->with($quoteId)->willReturnSelf();
        $this->orderDataFactoryMock->expects($this->once())->method('create')->willReturn($this->orderInterfaceMock);
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('sales_convert_quote_to_order', ['order' => $this->orderInterfaceMock, 'quote' => $quote]);
        $this->assertSame($this->orderInterfaceMock, $this->converter->convert($object, $data));
    }
}
