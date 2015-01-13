<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCatalogPriceRuleAppliedCatalogPage
 */
class AssertCatalogPriceRuleAppliedCatalogPage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Assert that Catalog Price Rule is applied for product(s) in Catalog
     * according to Priority(Priority/Stop Further Rules Processing)
     *
     * @param CatalogProductSimple $product
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param array $price
     * @return void
     */
    public function processAssert(
        CatalogProductSimple $product,
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        array $price
    ) {
        $cmsIndex->open();
        $categoryName = $product->getCategoryIds()[0];
        $productName = $product->getName();
        $cmsIndex->getTopmenu()->selectCategoryByName($categoryName);
        $productPriceBlock = $catalogCategoryView->getListProductBlock()->getProductPriceBlock($productName);
        $actualPrice['regular'] = $productPriceBlock->getRegularPrice();
        $actualPrice['special'] = $productPriceBlock->getSpecialPrice();
        $actualPrice['discount_amount'] = $actualPrice['regular'] - $actualPrice['special'];
        $diff = $this->verifyData($actualPrice, $price);
        \PHPUnit_Framework_Assert::assertTrue(
            empty($diff),
            implode(' ', $diff)
        );
    }

    /**
     * Check if arrays have equal values
     *
     * @param array $formData
     * @param array $fixtureData
     * @return array
     */
    protected function verifyData(array $formData, array $fixtureData)
    {
        $errorMessage = [];
        foreach ($formData as $key => $value) {
            if ($value != $fixtureData[$key]) {
                $errorMessage[] = "Data not equal."
                    . "\nExpected: " . $fixtureData[$key]
                    . "\nActual: " . $value;
            }
        }
        return $errorMessage;
    }

    /**
     * Text of catalog price rule visibility on catalog page (frontend)
     *
     * @return string
     */
    public function toString()
    {
        return 'Displayed catalog price rule data on catalog page(frontend) equals to passed from fixture.';
    }
}
