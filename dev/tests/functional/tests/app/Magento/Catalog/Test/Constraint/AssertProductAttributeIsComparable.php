<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Product\CatalogProductCompare;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Check whether there is an opportunity to compare products using given attribute.
 */
class AssertProductAttributeIsComparable extends AbstractConstraint
{
    /**
     * Check whether there is an opportunity to compare products using given attribute.
     *
     * @param InjectableFixture $product
     * @param CatalogProductAttribute $attribute
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param CatalogProductCompare $catalogProductCompare
     */
    public function processAssert(
        InjectableFixture $product,
        CatalogProductAttribute $attribute,
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        CatalogProductCompare $catalogProductCompare
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $catalogProductView->getViewBlock()->clickAddToCompare();
        $catalogProductCompare->open();
        $label = $attribute->hasData('manage_frontend_label')
            ? $attribute->getManageFrontendLabel()
            : $attribute->getFrontendLabel();

        \PHPUnit_Framework_Assert::assertTrue(
            in_array($label, $catalogProductCompare->getCompareProductsBlock()->getComparableAttributes()),
            'Attribute is absent on product compare page.'
        );
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Attribute is present on product compare page.';
    }
}
