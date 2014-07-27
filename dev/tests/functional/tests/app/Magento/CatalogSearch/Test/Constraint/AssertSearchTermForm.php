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

use Mtf\Constraint\AbstractConstraint;
use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchEdit;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchIndex;

/**
 * Class AssertSearchTermForm
 * Assert that after save a search term on edit term search page displays
 */
class AssertSearchTermForm extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Assert that after save a search term on edit term search page displays:
     *  - correct Search Query field passed from fixture
     *  - correct Store
     *  - correct Number of results
     *  - correct Number of Uses
     *  - correct Synonym For
     *  - correct Redirect URL
     *  - correct Display in Suggested Terms
     *
     * @param CatalogSearchIndex $indexPage
     * @param CatalogSearchEdit $editPage
     * @param CatalogSearchQuery $searchTerm
     * @return void
     */
    public function processAssert(
        CatalogSearchIndex $indexPage,
        CatalogSearchEdit $editPage,
        CatalogSearchQuery $searchTerm
    ) {
        $indexPage->open()->getGrid()->searchAndOpen(['search_query' => $searchTerm->getQueryText()]);
        $formData = $editPage->getForm()->getData($searchTerm);
        $fixtureData = $searchTerm->getData();

        \PHPUnit_Framework_Assert::assertEquals(
            $formData,
            $fixtureData,
            'This form "Search Term" does not match the fixture data.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'These form "Search Term" correspond to the fixture data.';
    }
}
