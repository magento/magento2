<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Backend\Test\Block\Dashboard\Tab\Products;

use Magento\Mtf\Client\Locator;
use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Ordered products grid on bestsellers tab on Dashboard
 */
class Ordered extends Grid
{
    // @codingStandardsIgnoreStart
    /**
     * Ordered products row in bestsellers grid
     *
     * @var string
     */
    protected $orderedProductsRow = '//table[@id="productsOrderedGrid_table"]/tbody/tr[td[contains(., "%s")] and td[contains(., "%d")] and td[contains(., "%d")]]';
    // @codingStandardsIgnoreEnd

    /**
     * Check if ordered product is in grid
     *
     * @param array $filter
     * @return bool
     */
    public function isProductVisible($filter)
    {
        if ($this->_rootElement->find(sprintf($this->orderedProductsRow, $filter['name'], $filter['price'],
            $filter['qty']), Locator::SELECTOR_XPATH)->isVisible()
        ) {
            return true;
        }
        return false;
    }
}
