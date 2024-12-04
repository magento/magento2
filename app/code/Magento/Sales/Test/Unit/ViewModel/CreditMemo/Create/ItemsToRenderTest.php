<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\ViewModel\CreditMemo\Create;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items as BlockItems;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\ViewModel\CreditMemo\Create\ItemsToRender;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test creditmemo items to render
 */
class ItemsToRenderTest extends TestCase
{
    /**
     * @var ItemsToRender
     */
    private $itemsToRender;

    /**
     * @var ConvertOrder|MockObject
     */
    private $converter;

    /**
     * @var BlockItems|MockObject
     */
    private $blockItems;

    /**
     * @var Creditmemo|MockObject
     */
    private $creditmemo;

    /**
     * @var CreditmemoItem|MockObject
     */
    private $creditmemoItem;

    /**
     * @var CreditmemoItem|MockObject
     */
    private $creditmemoItemParent;

    /**
     * @var OrderItem|MockObject
     */
    private $orderItem;

    /**
     * @var OrderItem|MockObject
     */
    private $orderItemParent;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->converter = $this->getMockBuilder(ConvertOrder::class)
            ->onlyMethods(['itemToCreditmemoItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->blockItems = $this->getMockBuilder(BlockItems::class)
            ->onlyMethods(['getCreditmemo'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemo = $this->getMockBuilder(Creditmemo::class)
            ->onlyMethods(['getAllItems', 'getId', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoItem = $this->getMockBuilder(CreditmemoItem::class)
            ->onlyMethods(['getOrderItem', 'getCreditMemo'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoItemParent = $this->getMockBuilder(CreditmemoItem::class)
            ->onlyMethods(['setCreditmemo', 'setParentId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoItemParent = $this->getMockBuilder(CreditmemoItem::class)
            ->addMethods(['getItemId', 'setStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderItem = $this->getMockBuilder(OrderItem::class)
            ->onlyMethods(['getParentItem','getQtyInvoiced','getQtyRefunded'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderItemParent = $this->getMockBuilder(OrderItem::class)
            ->onlyMethods(['getItemId'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var ObjectManager  */
        $objectManager = new ObjectManager($this);
        $this->itemsToRender = $objectManager->getObject(
            ItemsToRender::class,
            [
                'items' => $this->blockItems,
                'converter' => $this->converter
            ]
        );
    }

    /**
     * Test the behavior of getItems when the invoicing status changes.
     *
     * @dataProvider itemInvoicingDataProvider
     */
    public function testGetItemsBasedOnInvoicing($qtyInvoiced, $qtyRefunded, $expectedCount, $shouldContainItem): void
    {
        $this->blockItems->method('getCreditmemo')
            ->willReturn($this->creditmemo);
        $this->creditmemo->method('getAllItems')
            ->willReturn([$this->creditmemoItem]);
        $this->creditmemo->method('getId')
            ->willReturn(1);
        $this->creditmemoItem->method('getCreditmemo')
            ->willReturn($this->creditmemo);
        $this->creditmemo->method('getStoreId')
            ->willReturn(1);

        $this->orderItem->method('getQtyInvoiced')
            ->willReturn($qtyInvoiced);
        $this->orderItem->method('getQtyRefunded')
            ->willReturn($qtyRefunded);

        $this->creditmemoItem->method('getOrderItem')
            ->willReturn($this->orderItem);

        $items = $this->itemsToRender->getItems();

        $this->assertCount($expectedCount, $items);

        if ($shouldContainItem) {
            $this->assertContains($this->creditmemoItem, $items);
        } else {
            $this->assertNotContains($this->creditmemoItem, $items);
        }
    }

    /**
     * Data provider for testing the invoicing status.
     *
     * @return array
     */
    public static function itemInvoicingDataProvider(): array
    {
        return [
            // Test case: Item is invoiced, should be included
            [1.0, 0.0, 1, true],

            // Test case: Item is not invoiced, should not be included
            [0.0, 0.0, 0, false],
        ];
    }
}
