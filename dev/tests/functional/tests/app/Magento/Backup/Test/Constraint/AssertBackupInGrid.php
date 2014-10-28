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

namespace Magento\Backup\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\Backup\Test\Page\Adminhtml\BackupIndex;

/**
 * Class AssertBackupInGrid
 * Assert that created backup can be found in Backups grid
 */
class AssertBackupInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
