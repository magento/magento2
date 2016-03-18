<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSearchSynonymNotOnFrontend
 * Assert that you will be not redirected to url from dataset
 */
class AssertSearchSynonymNotOnFrontend extends AbstractConstraint
{
    /**
     * Assert that you will be not redirected to url from dataset
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogSearchQuery $searchTerm
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, BrowserInterface $browser, CatalogSearchQuery $searchTerm)
    {
        $cmsIndex->open()->getSearchBlock()->search($searchTerm->getSynonymFor());
        \PHPUnit_Framework_Assert::assertNotEquals(
            $browser->getUrl(),
            $searchTerm->getRedirect(),
            'Url in the browser corresponds to Url in fixture (redirect has been performed).'
            . PHP_EOL . 'Search term: "' . $searchTerm->getQueryText() . '"'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Search term was successfully removed (redirect by the synonym was not performed).';
    }
}
