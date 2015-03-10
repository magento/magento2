<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSearchTermMassActionNotOnFrontend
 * Assert that after mass delete a search term not redirect to url in dataset
 */
class AssertSearchTermMassActionNotOnFrontend extends AbstractConstraint
{
    /**
     * Assert that after mass delete a search term not redirect to url in dataset
     *
     * @param array $searchTerms
     * @param CmsIndex $cmsIndex
     * @param BrowserInterface $browser
     * @param AssertSearchTermNotOnFrontend $assertSearchTermNotOnFrontend
     * @return void
     */
    public function processAssert(
        array $searchTerms,
        CmsIndex $cmsIndex,
        BrowserInterface $browser,
        AssertSearchTermNotOnFrontend $assertSearchTermNotOnFrontend
    ) {
        foreach ($searchTerms as $term) {
            /** @var CatalogSearchQuery $term */
            $assertSearchTermNotOnFrontend->processAssert($cmsIndex, $browser, $term);
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'All search terms were successfully removed (redirects to the specified URL was not performed).';
    }
}
