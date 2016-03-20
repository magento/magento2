<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backup\Test\Block\Adminhtml;

use Magento\Backend\Test\Block\Widget\Grid as GridInterface;

/**
 * Class BackupGrid
 * Backups grid block
 */
class BackupGrid extends GridInterface
{
    /**
     * Backup row selector in grid
     *
     * @var string
     */
    protected $backupRow = 'td[data-column="time"]';

    /**
     * Check is backup row visible on grid
     *
     * @return bool
     */
    public function isBackupRowVisible()
    {
        return $this->_rootElement->find($this->backupRow)->isVisible();
    }
}
