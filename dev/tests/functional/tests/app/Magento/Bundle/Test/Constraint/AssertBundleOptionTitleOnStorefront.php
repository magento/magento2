<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Cms\Test\Page\CmsIndex;

/**
 * Assert that option title is correct in different stores on bundle product page.
 */
class AssertBundleOptionTitleOnStorefront extends AbstractConstraint
{
    /**
     * Assert that option title is correct on product view page.
     *
     * @param CatalogProductView $catalogProductView
     * @param CmsIndex $cmsIndex
     * @param BrowserInterface $browser
     * @param FixtureInterface $originalProduct
     * @param array $stores
     * @param array $optionTitles
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        CmsIndex $cmsIndex,
        BrowserInterface $browser,
        FixtureInterface $originalProduct,
        array $stores,
        array $optionTitles
    ) {
        $cmsIndex->open();
        $cmsIndex->getLinksBlock()->waitWelcomeMessage();
        foreach ($stores as $store) {
            $cmsIndex->getStoreSwitcherBlock()->selectStoreView($store->getName());
            $cmsIndex->getLinksBlock()->waitWelcomeMessage();
            $browser->open($_ENV['app_frontend_url'] . $originalProduct->getUrlKey() . '.html');
            $catalogProductView->getBundleViewBlock()->clickCustomize();
            \PHPUnit_Framework_Assert::assertTrue(
                $catalogProductView->getBundleViewBlock()->getBundleBlock()->isOptionVisible(
                    $optionTitles[$store->getStoreId()]
                ),
                sprintf(
                    'Option with title \'%s\' is missing in \'%s\' store view.',
                    $optionTitles[$store->getStoreId()],
                    $store->getName()
                )
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
        return 'Option title is correct on product view page.';
    }
}
