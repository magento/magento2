<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Category\Edit;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Mtf\Factory\Factory;

/**
 * Class CategoryForm
 * Category container block
 */
class CategoryForm extends FormTabs
{
    /**
     * Save button
     *
     * @var string
     */
    protected $saveButton = '[data-ui-id=category-edit-form-save-button]';

    /**
     * Category Products grid
     *
     * @var string
     */
    protected $productsGridBlock = '#catalog_category_products';

    /**
     * Get Category edit form
     *
     * @return \Magento\Catalog\Test\Block\Adminhtml\Category\Tab\ProductGrid
     */
    public function getCategoryProductsGrid()
    {
        return Factory::getBlockFactory()->getMagentoCatalogAdminhtmlCategoryTabProductGrid(
            $this->_rootElement->find($this->productsGridBlock)
        );
    }
}
