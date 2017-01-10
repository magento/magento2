<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchEdit;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSearchTermForm
 * Assert that after save a search term on edit term search page displays
 */
class AssertSearchTermForm extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Assert that after save a search term on edit term search page displays:
     *  - correct Search Query field passed from fixture
     *  - correct Store
     *  - correct Number of results
     *  - correct Number of Uses
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
