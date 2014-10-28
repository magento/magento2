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
use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;

/**
 * Class AssertSearchSynonymMassActionNotOnFrontend
 * Assert that you will be not redirected to url from dataset after mass delete search term
 */
class AssertSearchSynonymMassActionNotOnFrontend extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

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
