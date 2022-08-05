<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\ViewModel\CreditMemo\Create;

use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items as BlockItems;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\ViewModel\CreditMemo\Create\ItemsToRender;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
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
            ->onlyMethods(['getParentItem'])
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
     * Test get items
     */
    public function testGetItems(): void
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
        $this->creditmemoItem->method('getOrderItem')
            ->willReturn($this->orderItem);
        $this->orderItem->method('getParentItem')
            ->willReturn($this->orderItemParent);
        $this->orderItemParent->method('getItemId')
            ->willReturn(1);
        $this->converter->method('itemToCreditmemoItem')
            ->willReturn($this->creditmemoItemParent);

        $this->assertEquals(
            [$this->creditmemoItemParent, $this->creditmemoItem],
            $this->itemsToRender->getItems()
        );
    }
}
