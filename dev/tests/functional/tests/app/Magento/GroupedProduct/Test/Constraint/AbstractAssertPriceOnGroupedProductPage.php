<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertPriceOnProductPageInterface;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\GroupedProduct\Test\Fixture\GroupedProduct;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that displayed grouped price on product page equals passed from fixture.
 */
abstract class AbstractAssertPriceOnGroupedProductPage extends AbstractConstraint
{
    /**
     * Format error message
     *
     * @var string
     */
    protected $errorMessage;

    /**
     * Successful message
     *
     * @var string
     */
    protected $successfulMessage;

    /**
     * Verify product price on grouped product view page
     *
     * @param GroupedProduct $product
     * @param CatalogProductView $catalogProductView
     * @param AssertPriceOnProductPageInterface $object
     * @param BrowserInterface $browser
     * @param string $typePrice [optional]
     * @return bool|string
     */
    protected function processAssertPrice(
        GroupedProduct $product,
        CatalogProductView $catalogProductView,
        AssertPriceOnProductPageInterface $object,
        BrowserInterface $browser,
        $typePrice = ''
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        $groupedData = $product->getAssociated();
        /** @var CatalogProductSimple $subProduct */
        foreach ($groupedData['products'] as $key => $subProduct) {
            //Process assertions
            $catalogProductView->getGroupedProductViewBlock()->{'item' . $typePrice . 'PriceProductBlock'}(++$key);
            $object->setErrorMessage(sprintf($this->errorMessage, $subProduct->getData('name')));
            $object->assertPrice($subProduct, $catalogProductView->getGroupedProductViewBlock(), 'Grouped');
        }
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
