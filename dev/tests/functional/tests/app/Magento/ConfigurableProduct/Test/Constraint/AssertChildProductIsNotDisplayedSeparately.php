<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\CatalogSearch\Test\Page\CatalogsearchResult;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertChildProductIsNotDisplayedSeparately
 * Assert that products generated during configurable product creation - are not visible on frontend(by default).
 */
class AssertChildProductIsNotDisplayedSeparately extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'middle';
    /* end tags */

    /**
     * Assert that products generated during configurable product creation - are not visible on frontend(by default).
     *
     * @param CatalogSearchResult $catalogSearchResult
     * @param CmsIndex $cmsIndex
     * @param ConfigurableProduct $product
     * @return void
     */
    public function processAssert(
        CatalogsearchResult $catalogSearchResult,
        CmsIndex $cmsIndex,
        ConfigurableProduct $product
    ) {
        $configurableAttributesData = $product->getConfigurableAttributesData();
        $errors = [];

        $cmsIndex->open();
        foreach ($configurableAttributesData['matrix'] as $variation) {
            $product = $this->objectManager->create(
                'Magento\Catalog\Test\Fixture\CatalogProductSimple',
                [
                    'data' => [
                        'name' => $variation['name']
                    ]
                ]
            );

            $cmsIndex->getSearchBlock()->search($variation['sku']);
            $isVisibleProduct = $catalogSearchResult->getListProductBlock()->getProductItem($product)->isVisible();
            while (!$isVisibleProduct && $catalogSearchResult->getBottomToolbar()->nextPage()) {
                $isVisibleProduct = $catalogSearchResult->getListProductBlock()->getProductItem($product)->isVisible();
            }
            if ($isVisibleProduct) {
                $errors[] = sprintf(
                    "\nChild product with sku: \"%s\" is visible on frontend(by default).",
                    $variation['sku']
                );
            }
        }

        \PHPUnit_Framework_Assert::assertEmpty($errors, implode(' ', $errors));
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Child products generated during configurable product creation are not visible on frontend(by default)';
    }
}
