<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\CatalogSearch\Test\Page\AdvancedResult;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;

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
        $product = $catalogSearch->getDataFieldConfig('query_text')['source']->getFirstProduct();

        do {
            $isProductVisible = $resultPage->getListProductBlock()->getProductItem($product)->isVisible();
        } while (!$isProductVisible && $resultPage->getBottomToolbar()->nextPage());

        $productName = $product->getName();

        \PHPUnit\Framework\Assert::assertTrue($isProductVisible, "A product with name $productName was not found.");
        $resultPage->getListProductBlock()->getProductItem($product)->clickAddToCart();
        $catalogProductView->getViewBlock()->waitLoader();
        if (isset($product->getCheckoutData()['options'])) {
            $catalogProductView->getViewBlock()->addToCart($product);
            $message = $catalogProductView->getMessagesBlock()->getSuccessMessage();
        } else {
            $message = $resultPage->getMessagesBlock()->getSuccessMessage();
        }

        \PHPUnit\Framework\Assert::assertEquals(
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
