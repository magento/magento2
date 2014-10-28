<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Category\Edit;

use Mtf\Factory\Factory;
use Magento\Backend\Test\Block\Widget\FormTabs;

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
