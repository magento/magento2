<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Test\Constraint;

use Magento\Indexer\Test\Page\Adminhtml\IndexManagement;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert Indexer status in Index Management Page.
 */
class AssertIndexerStatus extends AbstractConstraint
{
    /**
     * Indexer status in Index Management Page.
     *
     * @var array
     */
    private $indexerStatus = [
        0 => 'REINDEX REQUIRED',
        1 => 'READY'
    ];

    /**
     * Assert Correct Indexer Status.
     *
     * @param IndexManagement $indexManagement
     * @param array $indexers
     * @param bool $expectedStatus
     * @return void
     */
    public function processAssert(IndexManagement $indexManagement, array $indexers, bool $expectedStatus = true)
    {
        $expectedStatus = $expectedStatus === false ? $this->indexerStatus[0] : $this->indexerStatus[1];
        $indexManagement->open();
        foreach ($indexers as $indexer) {
            $indexerStatus = $indexManagement->getMainBlock()->getIndexerStatus($indexer);
            \PHPUnit_Framework_Assert::assertEquals(
                $expectedStatus,
                $indexerStatus,
                'Wrong ' . $indexer . ' status is displayed.'
                . "\nExpected: " . $expectedStatus
                . "\nActual: " . $indexerStatus
            );
        }
    }

    /**
     * Returns indexers status.
     *
     * @return string
     */
    public function toString()
    {
        return 'Indexer status is correct.';
    }
}
