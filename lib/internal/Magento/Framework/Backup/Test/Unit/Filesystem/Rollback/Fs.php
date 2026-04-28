<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);


/**
 * Mock rollback worker for rolling back via local filesystem
 */
namespace Magento\Framework\Backup\Test\Unit\Filesystem\Rollback;

use Magento\Framework\Backup\Filesystem\Rollback\AbstractRollback;

class Fs extends AbstractRollback
{
    /**
     * Mock Files rollback implementation via local filesystem
     *
     * @see \Magento\Framework\Backup\Filesystem\Rollback\AbstractRollback::run()
     */
    public function run()
    {
    }
}
