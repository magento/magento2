<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;

/**
 * Assert that Catalog Price Rule is not applied on Product page.
 */
class AssertCatalogPriceRuleNotAppliedProductPage extends AbstractConstraint
{
    /**
     * Assert that Catalog Price Rule is not applied on Product page.
     *
     * @param CatalogProductView $catalogProductViewPage
     * @param CmsIndex $cmsIndexPage
     * @param CatalogCategoryView $catalogCategoryViewPage
     * @param array $products
     * @param Customer|null $customer
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductViewPage,
        CmsIndex $cmsIndexPage,
        CatalogCategoryView $catalogCategoryViewPage,
        array $products,
        Customer $customer = null
    ) {
        if ($customer !== null) {
            $this->objectManager->create(
                \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
                ['customer' => $customer]
            )->run();
        } else {
            $this->objectManager->create(\Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class)->run();
        }
        $cmsIndexPage->open();
        foreach ($products as $product) {
            $categoryName = $product->getCategoryIds()[0];
            $cmsIndexPage->getTopmenu()->selectCategoryByName($categoryName);
            $catalogCategoryViewPage->getListProductBlock()->getProductItem($product)->open();
            $productPriceBlock = $catalogProductViewPage->getViewBlock()->getPriceBlock();
            \PHPUnit_Framework_Assert::assertFalse(
                $productPriceBlock->isSpecialPriceVisible(),
                "Catalog price rule is applied!\n"
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Catalog price rule was not applied to products on product page.';
    }
}
