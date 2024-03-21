<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model;

/**
 * Search Result Applier interface.
 *
 * @deprecated - as the implementation has been reverted during the fix of ACP2E-748
 * @see \Magento\InventoryCatalog\Plugin\Catalog\Model\ResourceModel\Product\CollectionPlugin
 */
interface StockStatusApplierInterface
{

    /**
     * Set flag, if the request is originated from SearchResultApplier
     *
     * @param bool $status
     * @deprecated
     * @see \Magento\InventoryCatalog\Plugin\Catalog\Model\ResourceModel\Product\CollectionPlugin::beforeSetOrder
     */
    public function setSearchResultApplier(bool $status): void;

    /**
     * Get flag, if the request is originated from SearchResultApplier
     *
     * @return bool
     * @deprecated
     * @see \Magento\InventoryCatalog\Plugin\Catalog\Model\ResourceModel\Product\CollectionPlugin::beforeSetOrder
     */
    public function hasSearchResultApplier() : bool;
}
