<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\Constraint;

use Magento\Search\Test\Fixture\SynonymGroup;
use Magento\Search\Test\Page\Adminhtml\SynonymGroupIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that created Synonym Group can be found in grid.
 */
class AssertSynonymGroupInGrid extends AbstractConstraint
{
    /**
     * Filters array mapping.
     *
     * @var array
     */
    private $filter;

    /**
     * Assert that created Synonym Group can be found in grid via: synonyms.
     *
     * @param SynonymGroup $synonymGroup
     * @param SynonymGroupIndex $synonymGroupIndex
     * @param array|null $synonymFilter
     * @return void
     */
    public function processAssert(
        SynonymGroup $synonymGroup,
        SynonymGroupIndex $synonymGroupIndex,
        $synonymFilter = null
    ) {
        $synonymGroupIndex->open();

        $this->prepareFilter($synonymGroup, $synonymFilter);
        $synonymGroupIndex->getSynonymGroupGrid()->search($this->filter);

        \PHPUnit_Framework_Assert::assertTrue(
            $synonymGroupIndex->getSynonymGroupGrid()->isRowVisible($this->filter, false, false),
            'Synonym Group is absent in Synonym grid'
        );

        \PHPUnit_Framework_Assert::assertEquals(
            count($synonymGroupIndex->getSynonymGroupGrid()->getAllIds()),
            1,
            'There is more than one synonyms founded'
        );
    }

    /**
     * Prepare filter for search synonyms.
     *
     * @param SynonymGroup $synonymGroup
     * @param array|null $synonymFilter
     * @return void
     */
    private function prepareFilter(SynonymGroup $synonymGroup, $synonymFilter = null)
    {
        $data = $synonymGroup->getData();
        $this->filter = [
            'synonyms' => $data['synonyms'],
            'website_id' => isset($synonymFilter['data']['website'])
                ? $synonymFilter['data']['website']
                : '',
            'group_id' => isset($synonymFilter['data']['id'])
                ? $synonymFilter['data']['id']
                : '',
        ];
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Synonym Group is present in grid.';
    }
}
