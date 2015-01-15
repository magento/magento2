<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Category\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Catalog\Test\Block\Adminhtml\Category\Tab\ProductGrid;
use Mtf\Client\Element;

/**
 * Class Product
 * Products grid of Category Products tab
 */
class Product extends Tab
{
    /**
     * An element locator which allows to select entities in grid
     *
     * @var string
     */
    protected $selectItem = 'tbody tr .col-in_category';

    /**
     * Product grid locator
     *
     * @var string
     */
    protected $productGrid = '#catalog_category_products';

    /**
     * Fill category products
     *
     * @param array $fields
     * @param Element|null $element
     * @return void
     */
    public function fillFormTab(array $fields, Element $element = null)
    {
        if (!isset($fields['category_products'])) {
            return;
        }
        foreach ($fields['category_products']['source']->getData() as $productName) {
            $this->getProductGrid()->searchAndSelect(['name' => $productName]);
        }
    }

    /**
     * Returns role grid
     *
     * @return ProductGrid
     */
    public function getProductGrid()
    {
        return $this->blockFactory->create(
            'Magento\Catalog\Test\Block\Adminhtml\Category\Tab\ProductGrid',
            ['element' => $this->_rootElement->find($this->productGrid)]
        );
    }
}
