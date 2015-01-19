<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\CatalogSearch\Test\Page\CatalogsearchResult;
use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductSearchableBySku
 */
class AssertProductSearchableBySku extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Displays an error message
     *
     * @var string
     */
    protected $errorMessage = 'The product has not been found by SKU';

    /**
     * Message for passing test
     *
     * @var string
     */
    protected $successfulMessage = 'Product successfully found by SKU.';

    /**
     * Assert that product can be searched via Quick Search using searchable product attributes (Search by SKU)
     *
     * @param CatalogsearchResult $catalogSearchResult
     * @param CmsIndex $cmsIndex
     * @param FixtureInterface $product
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function processAssert(
        CatalogsearchResult $catalogSearchResult,
        CmsIndex $cmsIndex,
        FixtureInterface $product
    ) {
        $cmsIndex->open();
        $sku = ($product->hasData('sku') !== false) ? $product->getSku() : $product->getName();
        $cmsIndex->getSearchBlock()->search($sku);

        $quantityAndStockStatus = $product->getQuantityAndStockStatus();
        $stockStatus = isset($quantityAndStockStatus['is_in_stock'])
            ? $quantityAndStockStatus['is_in_stock']
            : null;

        $isVisible = $catalogSearchResult->getListProductBlock()->isProductVisible($product->getName());
        while (!$isVisible && $catalogSearchResult->getBottomToolbar()->nextPage()) {
            $isVisible = $catalogSearchResult->getListProductBlock()->isProductVisible($product->getName());
        }

        if ($product->getVisibility() === 'Catalog' || $stockStatus === 'Out of Stock') {
            $isVisible = !$isVisible;
            list($this->errorMessage, $this->successfulMessage) = [$this->successfulMessage, $this->errorMessage];
        }

        \PHPUnit_Framework_Assert::assertTrue(
            $isVisible,
            $this->errorMessage
        );
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
