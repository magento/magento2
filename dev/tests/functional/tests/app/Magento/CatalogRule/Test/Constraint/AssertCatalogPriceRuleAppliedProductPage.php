<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep;
use Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep;

/**
 * Assert that Catalog Price Rule is applied on Product page.
 */
class AssertCatalogPriceRuleAppliedProductPage extends AbstractConstraint
{
    /**
     * Assert that Catalog Price Rule is applied & it impacts on product's discount price on Product page.
     *
     * @param CatalogProductView $catalogProductViewPage
     * @param CmsIndex $cmsIndexPage
     * @param CatalogCategoryView $catalogCategoryViewPage
     * @param array $products
     * @param array $productPrice
     * @param Customer $customer
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductViewPage,
        CmsIndex $cmsIndexPage,
        CatalogCategoryView $catalogCategoryViewPage,
        array $products,
        array $productPrice,
        Customer $customer = null
    ) {
        if ($customer !== null) {
            $this->objectManager->create(
                LoginCustomerOnFrontendStep::class,
                ['customer' => $customer]
            )->run();
        } else {
            $this->objectManager->create(LogoutCustomerOnFrontendStep::class)->run();
        }

        $cmsIndexPage->open();
        foreach ($products as $key => $product) {
            $categoryName = $product->getCategoryIds()[0];
            $cmsIndexPage->getTopmenu()->selectCategoryByName($categoryName);
            $catalogCategoryViewPage->getListProductBlock()->getProductItem($product)->open();

            $catalogProductViewPage->getViewBlock()->waitLoader();
            $productPriceBlock = $catalogProductViewPage->getViewBlock()->getPriceBlock($product);
            $actualPrice['special'] = $productPriceBlock->getSpecialPrice();
            if ($productPrice[$key]['regular'] !== 'No') {
                $actualPrice['regular'] = $productPriceBlock->getOldPrice();
                $actualPrice['discount_amount'] = $actualPrice['regular'] - $actualPrice['special'];
                $actualPrice['price_from'] = $productPriceBlock->getPriceFrom();
                $actualPrice['price_to'] = $productPriceBlock->getPriceTo();
                $actualPrice['old_price_from'] = $productPriceBlock->getOldPriceFrom();
                $actualPrice['old_price_to'] = $productPriceBlock->getOldPriceTo();
            }
            $diff = $this->verifyData($productPrice[$key], $actualPrice);
            \PHPUnit\Framework\Assert::assertTrue(
                empty($diff),
                implode(' ', $diff)
            );
        }
    }

    /**
     * Check if arrays have equal values.
     *
     * @param array $fixtureData
     * @param array $formData
     * @return array
     */
    protected function verifyData(array $fixtureData, array $formData)
    {
        $errorMessage = [];
        foreach ($fixtureData as $key => $value) {
            if (isset($formData[$key]) && (float)$value !== (float)$formData[$key]) {
                $errorMessage[] = "Value " . $key . " is not equal."
                    . "\nExpected: " . $fixtureData[$key]
                    . "\nActual: " . $value . "\n";
            }
        }
        return $errorMessage;
    }

    /**
     * Text of catalog price rule visibility on product page (frontend).
     *
     * @return string
     */
    public function toString()
    {
        return 'Displayed catalog price rule data on product page(frontend) equals to passed from fixture.';
    }
}
