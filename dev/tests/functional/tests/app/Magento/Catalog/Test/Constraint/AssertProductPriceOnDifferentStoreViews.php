<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Cms\Test\Page\CmsIndex;

/**
 * Assert product price in different store views on product view page.
 */
class AssertProductPriceOnDifferentStoreViews extends AbstractConstraint
{
    /**
     * Assert that product name is correct on the storefront in different store views.
     *
     * @param CatalogProductView $catalogProductView
     * @param CmsIndex $cmsIndex
     * @param BrowserInterface $browser
     * @param FixtureInterface $initialProduct
     * @param array $stores
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        CmsIndex $cmsIndex,
        BrowserInterface $browser,
        FixtureInterface $initialProduct,
        array $stores
    ) {
        $browser->open($_ENV['app_frontend_url'] . $initialProduct->getUrlKey() . '.html');
        foreach ($stores as $store) {
            $cmsIndex->getStoreSwitcherBlock()->selectStoreView($store->getName());
            $cmsIndex->getLinksBlock()->waitWelcomeMessage();
            \PHPUnit_Framework_Assert::assertEquals(
                '9.99',
                $catalogProductView->getViewBlock()->getPriceBlock()->getPrice(),
                sprintf('Wrong product price is displayed for %s store view.', $store->getName())
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
        return 'Product name is correct on the storefront';
    }
}
