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
 * Check whether the attribute is visible on the frontend.
 */
class AssertProductAttributeDisplayingOnFrontend extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Check whether the attribute is visible on the frontend.
     *
     * @param InjectableFixture $product
     * @param CatalogProductAttribute $attribute
     * @param CatalogProductView $catalogProductView
     * @param Browser $browser
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
            in_array(
                $attribute->getFrontendLabel(),
                $catalogProductView->getAdditionalInformationBlock()->getAttributeLabels()
            ),
            'Attribute is not visible on product page in additional info block on frontend.'
        );
    }

    /**
     * Returns string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Attribute is visible on product page in additional info block on frontend.';
    }
}
