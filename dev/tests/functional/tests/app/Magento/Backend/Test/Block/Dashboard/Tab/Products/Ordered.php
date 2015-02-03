<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\Dashboard\Tab\Products;

use Magento\Mtf\Client\Locator;
use Magento\Backend\Test\Block\Widget\Grid;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Ordered products grid on bestsellers tab on Dashboard
 */
class Ordered extends Grid
{
    /**
     * Check if ordered product is in grid
     *
     * @param CatalogProductSimple $product
     * @return bool
     */
    public function isProductVisible(CatalogProductSimple $product)
    {
        $location = '//table[@id="productsOrderedGrid_table"]/tbody/tr[';
        $rowTemplate = 'td[contains(., "%s")]';
        $filter = [
            $product->getName(),
            $product->getPrice(),
            $product->getCheckoutData()['qty'],
        ];
        $rows = [];
        foreach ($filter as $value) {
            $rows[] = sprintf($rowTemplate, $value);
        }
        $location = $location . implode(' and ', $rows) . ']';

        return $this->_rootElement->find($location, Locator::SELECTOR_XPATH)->isVisible() ? true : false;
    }
}
