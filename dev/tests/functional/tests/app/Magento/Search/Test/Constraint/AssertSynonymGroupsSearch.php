<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\Constraint;

use Magento\Search\Test\Page\Adminhtml\SynonymGroupIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that synonym groups can be found by keywords.
 */
class AssertSynonymGroupsSearch extends AbstractConstraint
{
    /**
     * Assert that results of search by keywords are correct.
     *
     * @param array $synonymGroups
     * @param array $searchQueries
     * @param SynonymGroupIndex $synonymGroupIndex
     * @return void
     */
    public function processAssert(array $synonymGroups, array $searchQueries, SynonymGroupIndex $synonymGroupIndex)
    {
        $synonymGroupIndex->open();
        foreach ($searchQueries as $query) {
            $synonymGroupIndex->getSynonymGroupGrid()->fullTextSearch($query['query']);
            foreach ($query['results'] as $key => $result) {
                \PHPUnit_Framework_Assert::assertEquals(
                    $result,
                    $synonymGroupIndex->getSynonymGroupGrid()->isRowVisible(
                        ['synonyms' => $synonymGroups[$key]->getData()['synonyms']],
                        false,
                        false
                    ),
                    sprintf(
                        'Synonym Group with synonyms \'%s\' is %s in the grid. Search query: %s',
                        $synonymGroups[$key]->getData()['synonyms'],
                        $result ? 'absent' : 'present',
                        $query['query']
                    )
                );
            }
            $synonymGroupIndex->getSynonymGroupGrid()->resetFilter();
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Results of search by keyword are correct.';
    }
}
