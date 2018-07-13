<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Plugin\Option;

use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Model\OptionRepository;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Model\StockRegistryStorage;

/**
 * Flush stock item and stock status caches in StockRegistryStorage
 * after options will assign to bundle.
 */
class FlushStockRegistryStorageCache
{
    /**
     * @var StockRegistryStorage
     */
    private $stockRegistryStorage;

    /**
     * @param StockRegistryStorage $stockRegistryStorage
     */
    public function __construct(
        StockRegistryStorage $stockRegistryStorage
    ) {
        $this->stockRegistryStorage = $stockRegistryStorage;
    }

    /**
     * Flush stock cache for bundle product by id.
     *
     * @param OptionRepository $subject
     * @param int $result
     * @param ProductInterface $product
     * @param OptionInterface $option
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        OptionRepository $subject,
        int $result,
        ProductInterface $product,
        OptionInterface $option
    ) {
        $productId = $product->getId();
        $this->stockRegistryStorage->removeStockItem($productId);
        $this->stockRegistryStorage->removeStockStatus($productId);

        return $result;
    }
}
