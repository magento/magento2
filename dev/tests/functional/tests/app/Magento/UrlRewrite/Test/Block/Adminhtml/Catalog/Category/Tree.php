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

namespace Magento\UrlRewrite\Test\Block\Adminhtml\Catalog\Category;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;
use Magento\Catalog\Test\Fixture\CatalogCategory;

/**
 * Class Tree
 * Categories tree block
 */
class Tree extends Block
{
    /**
     * Locator value for  skip category button
     *
     * @var string
     */
    protected $skipCategoryButton = '[data-ui-id="catalog-product-edit-skip-categories"]';

    /**
     * Select category by its name
     *
     * @param string|CatalogCategory $category
     * @return void
     */
    public function selectCategory($category)
    {
        //TODO Remove this line after old fixture was deleted
        $categoryName = $category instanceof CatalogCategory ? $category->getName() : $category;
        if ($categoryName) {
            $this->_rootElement->find("//a[contains(text(),'{$categoryName}')]", Locator::SELECTOR_XPATH)->click();
        } else {
            $this->skipCategorySelection();
        }
    }

    /**
     * Skip category selection
     *
     * @return void
     */
    protected function skipCategorySelection()
    {
        $this->_rootElement->find($this->skipCategoryButton, Locator::SELECTOR_CSS)->click();
    }
}
