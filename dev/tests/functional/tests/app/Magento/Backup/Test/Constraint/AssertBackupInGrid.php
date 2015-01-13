<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backup\Test\Constraint;

use Magento\Backup\Test\Page\Adminhtml\BackupIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertBackupInGrid
 * Assert that created backup can be found in Backups grid
 */
class AssertBackupInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that one backup row is present in Backups grid
     *
     * @param BackupIndex $backupIndex
     * @return void
     */
    public function processAssert(BackupIndex $backupIndex)
    {
        \PHPUnit_Framework_Assert::assertTrue(
            $backupIndex->open()->getBackupGrid()->isBackupRowVisible(),
            'Backup is not present in grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Backup is present in grid.';
    }
}
