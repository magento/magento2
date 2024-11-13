<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Block\Sales\Order\Email\Items;

use Magento\Backend\Block\Template\Context;
use Magento\Downloadable\Block\Sales\Order\Email\Items\Downloadable;
use Magento\Downloadable\Model\Link\Purchased;
use Magento\Downloadable\Model\Link\PurchasedFactory;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\Downloadable\Test\Unit\Block\Sales\Order\Email\Items\Downloadable
 */
class DownloadableTest extends TestCase
{
    /**
     * @var Downloadable
     */
    protected $block;

    /**
     * @var PurchasedFactory|MockObject
     */
    protected $purchasedFactory;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $itemsFactory;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->purchasedFactory = $this->getMockBuilder(PurchasedFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->itemsFactory = $this->getMockBuilder(
            CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $purchasedLink = new \Magento\Downloadable\Model\Sales\Order\Link\Purchased(
            $this->purchasedFactory,
            $this->itemsFactory
        );

        $this->block = $objectManager->getObject(
            Downloadable::class,
            [
                'context' => $contextMock,
                'purchasedLink' => $purchasedLink
            ]
        );
    }

    public function testGetLinks()
    {
        $orderItem = $item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();
        $orderItem->method('getId')
            ->willReturn(1);
        $item = new DataObject(['order_item' => $orderItem]);
        $linkPurchased = $this->getMockBuilder(Purchased::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load'])
            ->getMock();
        $itemCollection =
            $this->getMockBuilder(Collection::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['addFieldToFilter'])
                ->getMock();

        $this->block->setData('item', $item);
        $this->purchasedFactory->expects($this->once())->method('create')->willReturn($linkPurchased);
        $linkPurchased->expects($this->once())->method('load')->with(1, 'order_item_id')->willReturnSelf();
        $this->itemsFactory->expects($this->once())->method('create')->willReturn($itemCollection);
        $itemCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('order_item_id', 1)
            ->willReturnSelf();

        $this->assertEquals($linkPurchased, $this->block->getLinks());
    }
}
