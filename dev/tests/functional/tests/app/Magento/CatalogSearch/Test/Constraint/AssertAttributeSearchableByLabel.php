<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\CatalogSearch\Test\Page\CatalogsearchResult;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;

/**
 * Assert that product attribute is searchable on Frontend.
 */
class AssertAttributeSearchableByLabel extends AbstractConstraint
{
    /**
     * Assert that product attribute is searchable on Frontend.
     *
     * @param CatalogProductAttribute $attribute
     * @param CmsIndex $cmsIndex
     * @param InjectableFixture $product
     * @param CatalogsearchResult $catalogSearchResult
     * @return void
     */
    public function processAssert(
        CatalogProductAttribute $attribute,
        CmsIndex $cmsIndex,
        InjectableFixture $product,
        CatalogsearchResult $catalogSearchResult
    ) {
        $cmsIndex->open();
        $searchValue = '';

        if ($attribute->getOptions() !== null) {
            foreach ($attribute->getOptions() as $option) {
                if ($option['is_default'] == 'Yes') {
                    $searchValue = $option['admin'];
                }
            }
        } elseif ($attribute->getDefaultValueTextarea() !== null) {
            $searchValue = $attribute->getDefaultValueTextarea();
        } elseif ($attribute->getDefaultValueYesno() !== null) {
            $searchValue = $attribute->getDefaultValueYesno();
        } elseif ($attribute->getDefaultValueText() !== null) {
            $searchValue = $attribute->getDefaultValueText();
        } elseif ($attribute->getDefaultValueDate() !== null) {
            $searchValue = $attribute->getDefaultValueDate();
        }

        $cmsIndex->getSearchBlock()->search($searchValue);

        $isVisible = $catalogSearchResult->getListProductBlock()->getProductItem($product)->isVisible();
        while (!$isVisible && $catalogSearchResult->getBottomToolbar()->nextPage()) {
            $isVisible = $catalogSearchResult->getListProductBlock()->getProductItem($product)->isVisible();
        }

        \PHPUnit_Framework_Assert::assertTrue($isVisible, 'Product attribute is not searchable on Frontend.');
    }

    /**
     * Returns string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product attribute is searchable on Frontend.';
    }
}
