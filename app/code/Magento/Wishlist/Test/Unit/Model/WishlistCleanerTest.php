<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Quote\Model\Product\QuoteItemsCleaner;
use Magento\Wishlist\Model\ResourceModel\Item as ItemResourceModel;
use Magento\Wishlist\Model\ResourceModel\Item\Option as ItemOptionResourceModel;
use Magento\Wishlist\Model\WishlistCleaner;
use PHPUnit\Framework\TestCase;

/**
 * Tests WishlistCleaner
 */
class WishlistCleanerTest extends TestCase
{
    /**
     * @var QuoteItemsCleaner
     */
    private $model;

    /**
     * @var ItemOptionResourceModel
     */
    private $itemOptionResourceModel;

    /**
     * @var ItemResourceModel
     */
    private $itemResourceModel;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->itemOptionResourceModel = $this->createMock(ItemOptionResourceModel::class);
        $this->itemResourceModel = $this->createMock(ItemResourceModel::class);
        $this->model = new WishlistCleaner($this->itemOptionResourceModel, $this->itemResourceModel);
    }

    /**
     * Asserts that wishlist items related to a specific product are deleted
     */
    public function testExecute()
    {
        $productId = 1;
        $itemTable = 'table_item';
        $itemOptionTable = 'table_item_option';
        $product = $this->createMock(ProductInterface::class);
        $product->expects($this->once())->method('getId')->willReturn($productId);
        $connection = $this->createMock(AdapterInterface::class);
        $this->itemResourceModel->expects($this->once())->method('getConnection')->willReturn($connection);
        $this->itemResourceModel->expects($this->once())->method('getMainTable')->willReturn($itemTable);
        $this->itemOptionResourceModel->expects($this->once())->method('getMainTable')->willReturn($itemOptionTable);
        $select = $this->createMock(Select::class);
        $connection->expects($this->once())->method('query')->with($select);
        $connection->expects($this->once())
            ->method('select')
            ->willReturn($select);
        $select->expects($this->once())
            ->method('from')
            ->with(['w_item' => $itemTable])
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('join')
            ->with(['w_item_option' => $itemOptionTable], 'w_item.wishlist_item_id = w_item_option.wishlist_item_id')
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('where')
            ->with('w_item_option.product_id = ?', $productId)
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('deleteFromSelect')
            ->with('w_item')
            ->willReturnSelf();

        $this->model->execute($product);
    }
}
