<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Quote\Model\Quote\Item;

/**
 * Class ToOrderItemTest
 */
class ToOrderItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\Quote\Item\ToOrderItem
     */
    protected $converter;

    /**
     * @var \Magento\Sales\Api\Data\OrderItemDataBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemBuilderMock;

    /**
     * @var \Magento\Framework\Object\Copy|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectCopyServiceMock;

    /**
     * @var \Magento\Quote\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteItemMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Catalog\Model\Product\Type\Simple|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productTypeMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemMock;

    public function setUp()
    {
        $this->orderItemBuilderMock = $this->getMock(
            'Magento\Sales\Api\Data\OrderItemDataBuilder',
            ['populateWithArray', 'create'],
            [],
            '',
            false
        );
        $this->objectCopyServiceMock = $this->getMock(
            'Magento\Framework\Object\Copy',
            [],
            [],
            '',
            false
        );
        $this->quoteItemMock = $this->getMock(
            'Magento\Quote\Model\Quote\Item',
            [],
            [],
            '',
            false
        );
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            [],
            [],
            '',
            false
        );
        $this->productTypeMock = $this->getMock(
            'Magento\Catalog\Model\Product\Type\Simple',
            [],
            [],
            '',
            false
        );
        $this->orderItemMock = $this->getMockForAbstractClass('Magento\Sales\Api\Data\OrderItemInterface');

        $this->converter = new \Magento\Quote\Model\Quote\Item\ToOrderItem(
            $this->orderItemBuilderMock,
            $this->objectCopyServiceMock
        );
    }

    /**
     * test for convert method
     */
    public function testConvert()
    {
        $this->quoteItemMock->expects($this->exactly(2))
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($this->productTypeMock);
        $this->productTypeMock->expects($this->once())
            ->method('getOrderOptions')
            ->with($this->productMock)
            ->willReturn(['option']);
        $this->objectCopyServiceMock->expects($this->at(0))
            ->method('getDataFromFieldset')
            ->with('quote_convert_item', 'to_order_item', $this->quoteItemMock)
            ->willReturn([]);
        $this->objectCopyServiceMock->expects($this->at(1))
            ->method('getDataFromFieldset')
            ->with('quote_convert_item', 'to_order_item_discount', $this->quoteItemMock)
            ->willReturn([]);
        $this->orderItemBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($this->orderItemMock);
        $this->assertInstanceOf(
            'Magento\Sales\Api\Data\OrderItemInterface',
            $this->converter->convert($this->quoteItemMock, [])
        );
    }
}
