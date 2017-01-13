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
     * Assert that created Synonym Group can be found in grid via: synonyms.
     *
     * @param SynonymGroup $synonymGroup
     * @param SynonymGroupIndex $synonymGroupIndex
     * @return void
     *
     */
    public function processAssert(SynonymGroup $synonymGroup, SynonymGroupIndex $synonymGroupIndex)
    {
        $synonymGroupIndex->open();
        $data = $synonymGroup->getData();
        $filter = [
            'synonyms' => $data['synonyms'],
        ];

        $synonymGroupIndex->getSynonymGroupGrid()->search($filter);

        \PHPUnit_Framework_Assert::assertTrue(
            $synonymGroupIndex->getSynonymGroupGrid()->isRowVisible($filter, false, false),
            'Synonym Group with '
            . 'synonyms \'' . $filter['synonyms'] . '\', '
            . 'is absent in Synonym grid.'
        );
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
