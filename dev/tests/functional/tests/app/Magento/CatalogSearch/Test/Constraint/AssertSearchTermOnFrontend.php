<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Mtf\Client\Browser;
use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Block\Search;
use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;

/**
 * Class AssertSearchTermOnFrontend
 * Assert that after save a search term
 */
class AssertSearchTermOnFrontend extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

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
     * @param Browser $browser
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, CatalogSearchQuery $searchTerm, Browser $browser)
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
