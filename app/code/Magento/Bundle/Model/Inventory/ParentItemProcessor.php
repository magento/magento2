<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Inventory;

use Magento\Catalog\Api\Data\ProductInterface as Product;
use Magento\CatalogInventory\Observer\ParentItemProcessorInterface;

/**
 * Bundle product stock item processor
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
     * @inheritdoc
     */
    public function process(Product $product)
    {
        $this->changeParentStockStatus->execute([$product->getId()]);
    }
}
