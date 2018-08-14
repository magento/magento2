<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Base part of row locator template
     *
     * @var string
     */
    protected $location = '//table[@id="productsOrderedGrid_table"]/tbody/tr';

    /**
     * Secondary part of row locator template
     *
     * @var string
     */
    protected $rowTemplate = 'td[contains(., "%s")]';

    /**
     * Check if ordered product is in grid
     *
     * @param CatalogProductSimple $product
     * @return bool
     */
    public function isProductVisible(CatalogProductSimple $product)
    {
        $filter = [
            $product->getName(),
            $product->getPrice(),
            $product->getCheckoutData()['qty'],
        ];
        $rows = [];
        foreach ($filter as $value) {
            $rows[] = sprintf($this->rowTemplate, $value);
        }
        $location = $this->location . '[' . implode(' and ', $rows) . ']';

        return $this->_rootElement->find($location, Locator::SELECTOR_XPATH)->isVisible();
    }
}
