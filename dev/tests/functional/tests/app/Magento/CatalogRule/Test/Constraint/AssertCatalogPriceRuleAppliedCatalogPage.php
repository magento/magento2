<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;

/**
 * Assert that Catalog Price Rule is applied for product(s) in Catalog.
 */
class AssertCatalogPriceRuleAppliedCatalogPage extends AbstractConstraint
{
    /**
     * Assert that Catalog Price Rule is applied for product(s) in Catalog
     * according to Priority(Priority/Stop Further Rules Processing).
     *
     * @param CmsIndex $cmsIndexPage
     * @param CatalogCategoryView $catalogCategoryViewPage
     * @param array $products
     * @param array $productPrice
     * @param Customer $customer
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndexPage,
        CatalogCategoryView $catalogCategoryViewPage,
        array $products,
        array $productPrice,
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
        foreach ($products as $key => $product) {
            $categoryName = $product->getCategoryIds()[0];
            $cmsIndexPage->getTopmenu()->selectCategoryByName($categoryName);
            $priceBlock = $catalogCategoryViewPage->getListProductBlock()->getProductItem($product)->getPriceBlock();
            \PHPUnit_Framework_Assert::assertTrue(
                $priceBlock->isVisible(),
                'Price block is not displayed for product ' . $product->getName()
            );
            $actualPrice['special'] = (float)$priceBlock->getSpecialPrice();
            if ($productPrice[$key]['regular'] !== 'No') {
                $actualPrice['regular'] = (float)$priceBlock->getOldPrice();
                $actualPrice['discount_amount'] = $actualPrice['regular'] - $actualPrice['special'];

                $actualPrice['price_from'] = (float)$priceBlock->getPriceFrom();
                $actualPrice['price_to'] = (float)$priceBlock->getPriceTo();
                $actualPrice['old_price_from'] = (float)$priceBlock->getOldPriceFrom();
                $actualPrice['old_price_to'] = (float)$priceBlock->getOldPriceTo();
            }
            $diff = $this->verifyData($productPrice[$key], $actualPrice);
            \PHPUnit_Framework_Assert::assertTrue(
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
     * Text of catalog price rule visibility on catalog page (frontend).
     *
     * @return string
     */
    public function toString()
    {
        return 'Displayed catalog price rule data on catalog page(frontend) equals to passed from fixture.';
    }
}
