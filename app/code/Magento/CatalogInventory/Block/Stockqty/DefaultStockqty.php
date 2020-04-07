<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Block\Stockqty;

/**
 * Product stock qty default block
 *
 * @api
 * @since 100.0.2
 *
 * @deprecated 2.3.0 Replaced with Multi Source Inventory
 * @link https://devdocs.magento.com/guides/v2.3/inventory/index.html
 * @link https://devdocs.magento.com/guides/v2.3/inventory/catalog-inventory-replacements.html
 */
class DefaultStockqty extends AbstractStockqty implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return $this->getProduct()->getIdentities();
    }

    /**
     * Retrieve manage stock value
     *
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStock()
    {
        return $this->stockRegistry->getStockItem($this->getProduct()->getId());
    }
}
