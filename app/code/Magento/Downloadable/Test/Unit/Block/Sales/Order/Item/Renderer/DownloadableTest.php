<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Block\Sales\Order\Item\Renderer;

use Magento\Backend\Block\Template\Context;
use Magento\Downloadable\Block\Sales\Order\Item\Renderer\Downloadable;
use Magento\Downloadable\Model\Link\Purchased;
use Magento\Downloadable\Model\Link\PurchasedFactory;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\Downloadable\Test\Unit\Block\Sales\Order\Item\Renderer\Downloadable
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
            ->setMethods(['create'])
            ->getMock();
        $this->itemsFactory = $this->getMockBuilder(
            CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->block = $objectManager->getObject(
            Downloadable::class,
            [
                'context' => $contextMock,
                'purchasedFactory' => $this->purchasedFactory,
                'itemsFactory' => $this->itemsFactory
            ]
        );
    }

    public function testGetLinks()
    {
        $item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $linkPurchased = $this->getMockBuilder(Purchased::class)
            ->disableOriginalConstructor()
            ->setMethods(['load'])
            ->getMock();
        $itemCollection =
            $this->getMockBuilder(Collection::class)
                ->disableOriginalConstructor()
                ->setMethods(['addFieldToFilter'])
                ->getMock();

        $this->block->setData('item', $item);
        $this->purchasedFactory->expects($this->once())->method('create')->willReturn($linkPurchased);
        $linkPurchased->expects($this->once())->method('load')->with('itemId', 'order_item_id')->willReturnSelf();
        $item->expects($this->any())->method('getId')->willReturn('itemId');
        $this->itemsFactory->expects($this->once())->method('create')->willReturn($itemCollection);
        $itemCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('order_item_id', 'itemId')
            ->willReturnSelf();

        $this->assertEquals($linkPurchased, $this->block->getLinks());
    }
}
