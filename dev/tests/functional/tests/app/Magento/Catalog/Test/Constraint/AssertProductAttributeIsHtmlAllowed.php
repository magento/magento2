<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Mtf\Client\Driver\Selenium\Browser;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\InjectableFixture;

/**
 * Check whether html tags are using in an attribute value.
 */
class AssertProductAttributeIsHtmlAllowed extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Check whether html tags are using in attribute value.
     * Checked tag structure <b><i>atttribute_default_value</p></i></b>
     *
     * @param InjectableFixture $product
     * @param CatalogProductAttribute $attribute
     * @param CatalogProductView $catalogProductView
     * @param Browser $browser
     * @throws \Exception
     * @return void
     */
    public function processAssert(
        InjectableFixture $product,
        CatalogProductAttribute $attribute,
        CatalogProductView $catalogProductView,
        Browser $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        \PHPUnit_Framework_Assert::assertTrue(
            $catalogProductView->getAdditionalInformationBlock()->hasHtmlTagInAttributeValue($attribute),
            'Attribute is not visible with HTML tags on frontend.'
        );
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Attribute is visible with HTML tags on frontend.';
    }
}
