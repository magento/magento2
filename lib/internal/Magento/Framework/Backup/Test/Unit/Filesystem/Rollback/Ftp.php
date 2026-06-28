<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);


/**
 * Mock Rollback worker for rolling back via ftp
 */
namespace Magento\Framework\Backup\Test\Unit\Filesystem\Rollback;

use Magento\Framework\Backup\Filesystem\Rollback\AbstractRollback;

class Ftp extends AbstractRollback
{
    /**
     * Mock Files rollback implementation via ftp
     *
     * @see \Magento\Framework\Backup\Filesystem\Rollback\AbstractRollback::run()
     */
    public function run()
    {
    }
}
