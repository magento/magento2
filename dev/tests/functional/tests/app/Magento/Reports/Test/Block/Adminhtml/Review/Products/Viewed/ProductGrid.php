<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Block\Adminhtml\Review\Products\Viewed;

use Magento\Backend\Test\Block\Widget\Grid;
use Mtf\Client\Element\Locator;

/**
 * Class ProductGrid
 * Product Views Report
 */
class ProductGrid extends Grid
{
    /**
     * Product in grid locator
     *
     * @var string
     */
    protected $product = './/*[contains(.,"%s") and *[contains(@class,"price") and contains(.,"%d")]]';

    /**
     * Count product views
     *
     * @var string
     */
    protected $productView = '/*[contains(@class,"qty")]';

    /**
     * Get views Results from Products Report grid
     *
     * @param array $products
     * @return array
     */
    public function getViewsResults(array $products)
    {
        $views = [];
        foreach ($products as $product) {
            $productLocator = sprintf($this->product . $this->productView, $product->getName(), $product->getPrice());
            $views[] = $this->_rootElement->find($productLocator, Locator::SELECTOR_XPATH)->getText();
        }
        return $views;
    }
}
