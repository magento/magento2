<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that deleted configurable attributes are absent on product page on frontend.
 */
class AssertConfigurableAttributesAbsentOnProductPage extends AbstractConstraint
{
    /**
     * Assert that deleted configurable attributes are absent on product page on frontend.
     *
     * @param CatalogProductAttribute[] $deletedProductAttributes
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param ConfigurableProduct $product
     * @return void
     */
    public function processAssert(
        array $deletedProductAttributes,
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        ConfigurableProduct $product
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $pageOptions = $catalogProductView->getViewBlock()->getOptions($product)['configurable_options'];

        foreach ($deletedProductAttributes as $attribute) {
            $attributeLabel = $attribute->getFrontendLabel();
            \PHPUnit_Framework_Assert::assertFalse(
                isset($pageOptions[$attributeLabel]),
                "Configurable attribute '$attributeLabel' found on product page on frontend."
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
        return "Configurable attributes are absent on product page on frontend.";
    }
}
