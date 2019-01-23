<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Model\ProductRepository;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\InventoryCatalog\Model\LocalCache\GetProductTypesBySkusCache;

/**
 * Clean local cache on service GetProductTypesBySkus when product saved or deleted.
 */
class CleanGetProductTypesBySkusCache
{
    /**
     * @var GetProductTypesBySkusCache
     */
    private $getProductTypesBySkusCache;

    /**
     * @param GetProductTypesBySkusCache $getProductTypesBySkusCache
     */
    public function __construct(
        GetProductTypesBySkusCache $getProductTypesBySkusCache
    ) {
        $this->getProductTypesBySkusCache = $getProductTypesBySkusCache;
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
        $this->getProductTypesBySkusCache->clean();

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
        $this->getProductTypesBySkusCache->clean();

        return $result;
    }
}
