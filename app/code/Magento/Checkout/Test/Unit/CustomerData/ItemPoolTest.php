<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\CustomerData;

use Magento\Checkout\CustomerData\ItemInterface;
use Magento\Checkout\CustomerData\ItemPool;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemPoolTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $objectManagerMock;

    /**
     * @var string
     */
    protected $defaultItemId = 'default_item_id';

    /**
     * @var string[]
     */
    protected $itemMap = [];

    /**
     * @var ItemPool
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->model = $objectManager->getObject(
            ItemPool::class,
            [
                'objectManager' => $this->objectManagerMock,
                'defaultItemId' => $this->defaultItemId,
                'itemMap' => $this->itemMap,
            ]
        );
    }

    public function testGetItemDataIfItemNotExistInMap()
    {
        $itemData = ['key' => 'value'];
        $productType = 'product_type';
        $quoteItemMock = $this->createMock(Item::class);
        $quoteItemMock->expects($this->once())->method('getProductType')->willReturn($productType);

        $itemMock = $this->getMockForAbstractClass(ItemInterface::class);
        $itemMock->expects($this->once())->method('getItemData')->with($quoteItemMock)->willReturn($itemData);

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->defaultItemId)
            ->willReturn($itemMock);

        $this->assertEquals($itemData, $this->model->getItemData($quoteItemMock));
    }

    public function testGetItemDataIfItemExistInMap()
    {
        $itemData = ['key' => 'value'];
        $productType = 'product_type';
        $this->itemMap[$productType] = 'product_id';

        $quoteItemMock = $this->createMock(Item::class);
        $quoteItemMock->expects($this->once())->method('getProductType')->willReturn($productType);

        $itemMock = $this->getMockForAbstractClass(ItemInterface::class);
        $itemMock->expects($this->once())->method('getItemData')->with($quoteItemMock)->willReturn($itemData);

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->itemMap[$productType])
            ->willReturn($itemMock);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            ItemPool::class,
            [
                'objectManager' => $this->objectManagerMock,
                'defaultItemId' => $this->defaultItemId,
                'itemMap' => $this->itemMap,
            ]
        );

        $this->assertEquals($itemData, $this->model->getItemData($quoteItemMock));
    }

    public function testGetItemDataIfItemNotValid()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('product_type doesn\'t extend \Magento\Checkout\CustomerData\ItemInterface');
        $itemData = ['key' => 'value'];
        $productType = 'product_type';
        $quoteItemMock = $this->createMock(Item::class);
        $quoteItemMock->expects($this->once())->method('getProductType')->willReturn($productType);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->defaultItemId)
            ->willReturn($this->createMock(Item::class));
        $this->assertEquals($itemData, $this->model->getItemData($quoteItemMock));
    }
}
