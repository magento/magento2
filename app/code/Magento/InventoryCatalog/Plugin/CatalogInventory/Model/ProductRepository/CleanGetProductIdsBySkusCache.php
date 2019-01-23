<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Model\ProductRepository;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\InventoryCatalog\Model\LocalCache\GetProductIdsBySkusCache;

/**
 * Clean local cache on service GetProductIdsBySkus when product saved or deleted.
 */
class CleanGetProductIdsBySkusCache
{
    /**
     * @var GetProductIdsBySkusCache
     */
    private $getProductIdsBySkusCache;

    /**
     * @param GetProductIdsBySkusCache $getProductIdsBySkusCache
     */
    public function __construct(
        GetProductIdsBySkusCache $getProductIdsBySkusCache
    ) {
        $this->getProductIdsBySkusCache = $getProductIdsBySkusCache;
    }

    /**
     * @param ProductRepository $subject
     * @param ProductInterface $result
     * @param ProductInterface $product
     * @param bool $saveOptions
     * @return ProductInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        ProductRepository $subject,
        ProductInterface $result,
        ProductInterface $product,
        $saveOptions = false
    ): ProductInterface {
        $this->getProductIdsBySkusCache->clean();

        return $result;
    }

    /**
     * @param ProductRepository $subject
     * @param bool $result
     * @param ProductInterface $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        ProductRepository $subject,
        bool $result,
        ProductInterface $product
    ): bool {
        $this->getProductIdsBySkusCache->clean();

        return $result;
    }
}
