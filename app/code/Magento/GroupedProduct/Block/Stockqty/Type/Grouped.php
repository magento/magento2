<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Block\Stockqty\Type;

use Magento\Framework\DataObject\IdentityInterface;

/**
 * Product stock qty block for grouped product type
 *
 * @api
 * @since 100.0.2
 */
class Grouped extends \Magento\CatalogInventory\Block\Stockqty\Composite implements IdentityInterface
{
    /**
     * Retrieve child products
     *
     * @return array
     */
    protected function _getChildProducts()
    {
        return $this->getProduct()->getTypeInstance()->getAssociatedProducts($this->getProduct());
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->getChildProducts() as $item) {
            $identities[] = $item->getIdentities();
        }
        return array_merge([], ...$identities);
    }
}
