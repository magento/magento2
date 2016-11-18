<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\CatalogSearch\Test\Page\AdvancedResult;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Fixture\CatalogProductVirtual;
use Magento\GroupedProduct\Test\Fixture\GroupedProduct;

/**
 * Assert product can be added to cart from search results page.
 */
class AssertProductAddedToCartFromSearchResults extends AbstractConstraint
{
    /**
     * Success add to cart message.
     */
    const SUCCESS_MESSAGE = 'You added %s to your shopping cart.';

    /**
     * Assert product can be added to cart from search results page.
     *
     * @param CatalogSearchQuery $catalogSearch
     * @param AdvancedResult $resultPage
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    public function processAssert(
        CatalogSearchQuery $catalogSearch,
        AdvancedResult $resultPage,
        CatalogProductView $catalogProductView
    ) {
        $product = $catalogSearch->getDataFieldConfig('query_text')['source']->getProduct();

        $isProductVisible = $resultPage->getListProductBlock()->getProductItem($product)->isVisible();
        while (!$isProductVisible && $resultPage->getBottomToolbar()->nextPage()) {
            $isProductVisible = $resultPage->getListProductBlock()->getProductItem($product)->isVisible();
        }
        $productName = $product->getName();

        \PHPUnit_Framework_Assert::assertTrue($isProductVisible, "A product with name $productName was not found.");
        $resultPage->getListProductBlock()->getProductItem($product)->clickAddToCart();

        $message = '';
        if ($product instanceof CatalogProductSimple || $product instanceof CatalogProductVirtual
            || $product instanceof GroupedProduct) {
            $message = $resultPage->getMessagesBlock()->getSuccessMessage();
        } else {
            $catalogProductView->getViewBlock()->addToCart($product);
            $message = $catalogProductView->getMessagesBlock()->getSuccessMessage();
        }
        \PHPUnit_Framework_Assert::assertEquals(
            sprintf(self::SUCCESS_MESSAGE, $productName),
            $message
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product was successfully added to cart from the search results page.';
    }
}
