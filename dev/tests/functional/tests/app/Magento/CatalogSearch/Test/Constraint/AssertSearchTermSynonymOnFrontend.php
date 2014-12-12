<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSearchTermSynonymOnFrontend
 * Assert that you will be redirected to url from dataset
 */
class AssertSearchTermSynonymOnFrontend extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Assert that you will be redirected to url from dataset
     *
     * @param CmsIndex $cmsIndex
     * @param Browser $browser
     * @param CatalogSearchQuery $searchTerm
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, Browser $browser, CatalogSearchQuery $searchTerm)
    {
        $cmsIndex->open()->getSearchBlock()->search($searchTerm->getSynonymFor());
        $windowUrl = $browser->getUrl();
        $redirectUrl = $searchTerm->getRedirect();
        \PHPUnit_Framework_Assert::assertEquals(
            $windowUrl,
            $redirectUrl,
            'Redirect by synonym was not executed.'
            . PHP_EOL . "Expected: " . $redirectUrl
            . PHP_EOL . "Actual: " . $windowUrl
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Redirect by synonym executed successfully.';
    }
}
