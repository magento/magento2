<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\Catalog\Test\Block\Search;
use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSearchTermOnFrontend
 * Assert that after save a search term
 */
class AssertSearchTermOnFrontend extends AbstractConstraint
{
    /**
     * Search block on CMS index page
     *
     * @var Search
     */
    protected $searchBlock;

    /**
     * Assert that after save a search term:
     *  - it displays in the Search field at the top of the page if type set of characters passed from fixture
     *  - after click 'Go' of Search field opens a results page if it was not specified Redirect URL
     *  - after click 'Go' of Search field a customer search redirects to a specific page (passed from fixture)
     *    if it was specified Redirect URL
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogSearchQuery $searchTerm
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, CatalogSearchQuery $searchTerm, BrowserInterface $browser)
    {
        $errors = [];
        $this->searchBlock = $cmsIndex->open()->getSearchBlock();

        if ($searchTerm->hasData('display_in_terms') && $searchTerm->getDisplayInTerms() === 'Yes') {
            $errors = $this->checkSuggestSearch($searchTerm);
        }

        $this->searchBlock->search($searchTerm->getQueryText());
        $windowUrl = $browser->getUrl();
        $redirectUrl = $searchTerm->getRedirect();
        if ($windowUrl !== $redirectUrl) {
            $errors[] = '- url window (' . $windowUrl . ') does not match the url redirect(' . $redirectUrl . ')';
        }

        \PHPUnit_Framework_Assert::assertEmpty(
            $errors,
            'When checking on the frontend "Search terms" arose following errors:' . PHP_EOL . implode(PHP_EOL, $errors)
        );
    }

    /**
     * Check suggest block visibility
     *
     * @param CatalogSearchQuery $searchTerm
     * @return array
     */
    protected function checkSuggestSearch(CatalogSearchQuery $searchTerm)
    {
        $queryText = $searchTerm->getQueryText();
        $this->searchBlock->fillSearch($queryText);
        if ($searchTerm->hasData('num_results')) {
            $isVisible = $this->searchBlock->isSuggestSearchVisible(
                $queryText,
                $searchTerm->getNumResults()
            );
        } else {
            $isVisible = $this->searchBlock->isSuggestSearchVisible($queryText);
        }

        return $isVisible ? [] : ['- block "Suggest Search" when searching was not found'];
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Checking "Search terms" on frontend successful.';
    }
}
