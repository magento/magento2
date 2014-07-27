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

use Mtf\Client\Browser;
use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;

/**
 * Class AssertCategoryIsNotIncludeInMenu
 * Assert that the category is no longer available on the top menu bar
 */
class AssertCategoryIsNotIncludeInMenu extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that the category is no longer available on the top menu bar
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogCategory $category
     * @param Browser $browser
     * @param CatalogCategoryView $categoryView
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogCategory $category,
        Browser $browser,
        CatalogCategoryView $categoryView
    ) {
        $cmsIndex->open();
        \PHPUnit_Framework_Assert::assertFalse(
            $cmsIndex->getTopmenu()->isCategoryVisible($category->getName()),
            'Category can be accessed from the navigation bar in the frontend.'
        );

        $browser->open($_ENV['app_frontend_url'] . $category->getUrlKey() . '.html');
        \PHPUnit_Framework_Assert::assertEquals(
            $category->getName(),
            $categoryView->getTitleBlock()->getTitle(),
            'Wrong page is displayed.'
        );
        if (isset($category->getDataFieldConfig('category_products')['source'])) {
            $products = $category->getDataFieldConfig('category_products')['source']->getProducts();
            foreach ($products as $productFixture) {
                \PHPUnit_Framework_Assert::assertTrue(
                    $categoryView->getListProductBlock()->isProductVisible($productFixture->getName()),
                    "Products '{$productFixture->getName()}' not find."
                );
            }
        }
    }

    /**
     * Category is no longer available on the top menu bar, but can be viewed by URL with all assigned products
     *
     * @return string
     */
    public function toString()
    {
        return 'Category is not on the top menu bar, but can be viewed by URL.';
    }
}
