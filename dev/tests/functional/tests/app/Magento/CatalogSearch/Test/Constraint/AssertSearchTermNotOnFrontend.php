<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSearchTermNotOnFrontend
 * Assert that after delete a search term not redirect to url in dataset
 */
class AssertSearchTermNotOnFrontend extends AbstractConstraint
{
    /**
     * Assert that after delete a search term not redirect to url in dataset
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogSearchQuery $searchTerm
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, BrowserInterface $browser, CatalogSearchQuery $searchTerm)
    {
        $queryText = $searchTerm->getQueryText();
        $cmsIndex->open()->getSearchBlock()->search($queryText);
        \PHPUnit\Framework\Assert::assertNotEquals(
            $browser->getUrl(),
            $searchTerm->getRedirect(),
            'Url in the browser corresponds to Url in fixture (redirect has been performed).'
            . PHP_EOL . 'Search term: "' . $queryText . '"'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Search term was successfully removed (redirects to the specified URL was not performed).';
    }
}
