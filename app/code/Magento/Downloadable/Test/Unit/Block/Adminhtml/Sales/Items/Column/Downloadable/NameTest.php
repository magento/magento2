<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Block\Adminhtml\Sales\Items\Column\Downloadable;

use Magento\Backend\Block\Template\Context;
use Magento\Downloadable\Block\Adminhtml\Sales\Items\Column\Downloadable\Name;
use Magento\Downloadable\Model\Link\Purchased;
use Magento\Downloadable\Model\Link\PurchasedFactory;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\Downloadable\Block\Adminhtml\Sales\Items\Column\Downloadable\Name
 */
class NameTest extends TestCase
{
    /**
     * @var Name
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
        $contextMock = $this->createMock(Context::class);
        $this->purchasedFactory = $this->createPartialMock(PurchasedFactory::class, ['create']);
        $this->itemsFactory = $this->createPartialMock(CollectionFactory::class, ['create']);

        $this->block = $objectManager->getObject(
            Name::class,
            [
                'context' => $contextMock,
                'purchasedFactory' => $this->purchasedFactory,
                'itemsFactory' => $this->itemsFactory
            ]
        );
    }

    public function testGetLinks()
    {
        $item = $this->createPartialMock(Item::class, ['getId']);
        $linkPurchased = $this->createPartialMock(Purchased::class, ['load']);
        $itemCollection = $this->createPartialMock(Collection::class, ['addFieldToFilter']);

        $this->block->setData('item', $item);
        $this->purchasedFactory->expects($this->once())->method('create')->willReturn($linkPurchased);
        $linkPurchased->expects($this->once())->method('load')->with('itemId', 'order_item_id')->willReturnSelf();
        $item->method('getId')->willReturn('itemId');
        $this->itemsFactory->expects($this->once())->method('create')->willReturn($itemCollection);
        $itemCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('order_item_id', 'itemId')
            ->willReturnSelf();

        $this->assertEquals($linkPurchased, $this->block->getLinks());
    }
}
