<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Mock Rollback worker for rolling back via ftp
 */
namespace Magento\Framework\Backup\Test\Unit\Filesystem\Rollback;

class Ftp extends \Magento\Framework\Backup\Filesystem\Rollback\AbstractRollback
{
    /**
     * Mock Files rollback implementation via ftp
     *
     * @see \Magento\Framework\Backup\Filesystem\Rollback\AbstractRollback::run()
     */
    public function run()
    {
        return;
    }
}
