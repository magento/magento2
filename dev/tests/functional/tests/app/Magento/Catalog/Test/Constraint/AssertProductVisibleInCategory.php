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

namespace Magento\Catalog\Test\Constraint;

use Mtf\Fixture\FixtureInterface;
use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;

/**
 * Class AssertProductVisibleInCategory
 */
class AssertProductVisibleInCategory extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Displays an error message
     *
     * @var string
     */
    protected $errorMessage = 'Product is absent on category page.';

    /**
     * Message for passing test
     *
     * @var string
     */
    protected $successfulMessage = 'Product is visible in the assigned category.';

    /**
     * Assert that product is visible in the assigned category
     *
     * @param CatalogCategoryView $catalogCategoryView
     * @param CmsIndex $cmsIndex
     * @param FixtureInterface $product
     * @param CatalogCategory|null $category
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function processAssert(
        CatalogCategoryView $catalogCategoryView,
        CmsIndex $cmsIndex,
        FixtureInterface $product,
        CatalogCategory $category = null
    ) {
        $categoryName = $product->hasData('category_ids') ? $product->getCategoryIds()[0] : $category->getName();
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($categoryName);

        $isProductVisible = $catalogCategoryView->getListProductBlock()->isProductVisible($product->getName());
        while (!$isProductVisible && $catalogCategoryView->getBottomToolbar()->nextPage()) {
            $isProductVisible = $catalogCategoryView->getListProductBlock()->isProductVisible($product->getName());
        }

        if (($product->getVisibility() === 'Search') || ($this->getStockStatus($product) === 'Out of Stock')) {
            $isProductVisible = !$isProductVisible;
            $this->errorMessage = 'Product found in this category.';
            $this->successfulMessage = 'Asserts that the product could not be found in this category.';
        }

        \PHPUnit_Framework_Assert::assertTrue(
            $isProductVisible,
            $this->errorMessage
        );
    }

    /**
     * Getting is in stock status
     *
     * @param FixtureInterface $product
     * @return string|null
     */
    protected function getStockStatus(FixtureInterface $product)
    {
        $quantityAndStockStatus = $product->getQuantityAndStockStatus();
        return isset($quantityAndStockStatus['is_in_stock']) ? $quantityAndStockStatus['is_in_stock'] : null;
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return $this->successfulMessage;
    }
}
