<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSearchSynonymMassActionNotOnFrontend
 * Assert that you will be not redirected to url from dataset after mass delete search term
 */
class AssertSearchSynonymMassActionNotOnFrontend extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Assert that you will be not redirected to url from dataset after mass delete search term
     *
     * @param array $searchTerms
     * @param CmsIndex $cmsIndex
     * @param Browser $browser
     * @param AssertSearchSynonymNotOnFrontend $assertSearchSynonymNotOnFrontend
     * @return void
     */
    public function processAssert(
        array $searchTerms,
        CmsIndex $cmsIndex,
        Browser $browser,
        AssertSearchSynonymNotOnFrontend $assertSearchSynonymNotOnFrontend
    ) {
        foreach ($searchTerms as $term) {
            $assertSearchSynonymNotOnFrontend->processAssert($cmsIndex, $browser, $term);
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'All search terms were successfully removed (redirect by the synonym was not performed).';
    }
}
