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
 * @since 2.0.0
 */
class DefaultStockqty extends AbstractStockqty implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Render block HTML
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getIdentities()
    {
        return $this->getProduct()->getIdentities();
    }
}
