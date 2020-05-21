<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
