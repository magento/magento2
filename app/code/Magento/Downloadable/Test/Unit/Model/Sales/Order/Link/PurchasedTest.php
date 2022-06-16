<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Model\Sales\Order\Link;

use Magento\Downloadable\Model\Link\Purchased as PurchasedEntity;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection;
use Magento\Downloadable\Model\Sales\Order\Link\Purchased;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Downloadable\Model\Link\PurchasedFactory;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory;

/**
 * Test order purchased link resolver
 */
class PurchasedTest extends TestCase
{
    /**
     * @var PurchasedFactory|MockObject
     */
    private $linkPurchasedFactory;
    /**
     * @var CollectionFactory|MockObject
     */
    private $linkPurchasedItemCollectionFactory;
    /**
     * @var Purchased
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->linkPurchasedFactory = $this->getMockBuilder(PurchasedFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->linkPurchasedItemCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->model = new Purchased(
            $this->linkPurchasedFactory,
            $this->linkPurchasedItemCollectionFactory
        );
    }

    /**
     * @param bool $hasChildItem
     * @param int $expectedItemId
     * @param array $itemData
     * @param array $childItemData
     * @dataProvider getLinkDataProvider
     */
    public function testGetLink(
        bool $hasChildItem,
        int $expectedItemId,
        array $itemData,
        array $childItemData = []
    ): void {
        /** @var Item $orderItem */
        $orderItem = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $orderItem->addData($itemData);
        /** @var Item $childOrderItem */
        $childOrderItem = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $childOrderItem->addData($childItemData);
        if ($hasChildItem) {
            $orderItem->addChildItem($childOrderItem);
        }
        $linkPurchased = $this->getMockBuilder(PurchasedEntity::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load'])
            ->getMock();
        $itemCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addFieldToFilter'])
            ->getMock();
        $this->linkPurchasedFactory->method('create')
            ->willReturn($linkPurchased);
        $linkPurchased->method('load')
            ->with($expectedItemId, 'order_item_id')
            ->willReturnSelf();
        $this->linkPurchasedItemCollectionFactory->method('create')
            ->willReturn($itemCollection);
        $itemCollection->method('addFieldToFilter')
            ->with('order_item_id', $expectedItemId)
            ->willReturnSelf();

        $this->assertEquals($linkPurchased, $this->model->getLink($orderItem));
        $this->assertEquals($linkPurchased, $this->model->getLink(new DataObject(['order_item' => $orderItem])));
    }

    /**
     * @return array[]
     */
    public function getLinkDataProvider(): array
    {
        return [
            [
                false,
                1,
                [
                    'id' => 1,
                    'product_type' => 'downloadable'
                ],
            ],
            [
                true,
                2,
                [
                    'id' => 1,
                    'product_type' => 'configurable'
                ],
                [
                    'id' => 2,
                    'product_type' => 'downloadable'
                ],
            ],
            [
                true,
                1,
                [
                    'id' => 1,
                    'product_type' => 'configurable'
                ],
                [
                    'id' => 2,
                    'product_type' => 'virtual'
                ],
            ]
        ];
    }
}
