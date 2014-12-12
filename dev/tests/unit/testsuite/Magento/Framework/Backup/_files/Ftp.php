<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Mock Rollback worker for rolling back via ftp
 */
namespace Magento\Framework\Backup\Filesystem\Rollback;

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
