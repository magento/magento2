<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\ViewModel\CreditMemo\Create;

use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items as BlockItems;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\ViewModel\CreditMemo\Create\ItemsToRender;
use PHPUnit\Framework\MockObject\MockObject;

class ItemsToRenderTest extends \PHPUnit\Framework\TestCase
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

    protected function setUp(): void
    {
        $this->converter = $this->getMockBuilder(ConvertOrder::class)
            ->setMethods(['itemToCreditmemoItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->blockItems = $this->getMockBuilder(BlockItems::class)
            ->setMethods(['getCreditmemo'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemo = $this->getMockBuilder(Creditmemo::class)
            ->setMethods(['getAllItems', 'getId', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoItem = $this->getMockBuilder(CreditmemoItem::class)
            ->setMethods(['getOrderItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoItemParent = $this->getMockBuilder(CreditmemoItem::class)
            ->setMethods(['getItemId', 'setCreditmemo', 'setParentId', 'setStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderItem = $this->getMockBuilder(OrderItem::class)
            ->setMethods(['getParentItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderItemParent = $this->getMockBuilder(OrderItem::class)
            ->setMethods(['getItemId'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager  */
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->itemsToRender = $objectManager->getObject(
            ItemsToRender::class,
            [
                'items' => $this->blockItems,
                'converter' => $this->converter
            ]
        );
    }

    public function testGetItems()
    {
        $this->blockItems->expects($this->any())
            ->method('getCreditmemo')
            ->willReturn($this->creditmemo);
        $this->creditmemo->expects($this->any())
            ->method('getAllItems')
            ->willReturn([$this->creditmemoItem]);
        $this->creditmemo->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->creditmemo->expects($this->any())
            ->method('getStoreId')
            ->willReturn(1);
        $this->creditmemoItem->expects($this->any())
            ->method('getOrderItem')
            ->willReturn($this->orderItem);
        $this->orderItem->expects($this->any())
            ->method('getParentItem')
            ->willReturn($this->orderItemParent);
        $this->orderItemParent->expects($this->any())
            ->method('getItemId')
            ->willReturn(1);
        $this->converter->expects($this->any())
            ->method('itemToCreditmemoItem')
            ->willReturn($this->creditmemoItemParent);
        $this->creditmemoItemParent->expects($this->any())
            ->method('setCreditmemo')
            ->willReturn($this->creditmemo);
        $this->creditmemoItemParent->expects($this->any())
            ->method('setParentId')
            ->willReturn(1);
        $this->creditmemoItemParent->expects($this->any())
            ->method('setStoreId')
            ->willReturn(1);

        $this->assertEquals(
            [$this->creditmemoItemParent, $this->creditmemoItem],
            $this->itemsToRender->getItems()
        );
    }
}
