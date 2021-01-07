<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Model\Inventory;

use Magento\Catalog\Api\Data\ProductInterface as Product;
use Magento\CatalogInventory\Observer\ParentItemProcessorInterface;

/**
 * Process parent stock item for grouped product
 */
class ParentItemProcessor implements ParentItemProcessorInterface
{
    /**
     * @var ChangeParentStockStatus
     */
    private $changeParentStockStatus;

    /**
     * @param ChangeParentStockStatus $changeParentStockStatus
     */
    public function __construct(
        ChangeParentStockStatus $changeParentStockStatus
    ) {
        $this->changeParentStockStatus = $changeParentStockStatus;
    }

    /**
     * Process parent products
     *
     * @param Product $product
     * @return void
     */
    public function process(Product $product)
    {
        $this->changeParentStockStatus->execute((int)$product->getId());
    }
}
