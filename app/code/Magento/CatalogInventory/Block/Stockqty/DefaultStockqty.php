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
 * @deprecated CatalogInventory will be replaced by Multi-Source Inventory (MSI)
 *             see https://github.com/magento-engcom/msi/wiki/Technical-Vision.-Catalog-Inventory
 */
class DefaultStockqty extends AbstractStockqty implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->isMsgVisible()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return $this->getProduct()->getIdentities();
    }
}
