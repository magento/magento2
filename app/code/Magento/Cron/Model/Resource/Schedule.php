<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\Resource;

/**
 * Schedule resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Schedule extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('cron_schedule', 'schedule_id');
    }

    /**
     * If job is currently in $currentStatus, set it to $newStatus
     * and return true. Otherwise, return false and do not change the job.
     * This method is used to implement locking for cron jobs.
     *
     * @param string $scheduleId
     * @param string $newStatus
     * @param string $currentStatus
     * @return bool
     */
    public function trySetJobStatusAtomic($scheduleId, $newStatus, $currentStatus)
    {
        $write = $this->_getWriteAdapter();
        $result = $write->update(
            $this->getTable('cron_schedule'),
            ['status' => $newStatus],
            ['schedule_id = ?' => $scheduleId, 'status = ?' => $currentStatus]
        );
        if ($result == 1) {
            return true;
        }
        return false;
    }
}
