<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel;

/**
 * Schedule resource
 *
 * @api
 * @since 100.0.2
 */
class Schedule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
     * Sets new schedule status only if it's in the expected current status.
     *
     * If schedule is currently in $currentStatus, set it to $newStatus and
     * return true. Otherwise, return false.
     *
     * @param string $scheduleId
     * @param string $newStatus
     * @param string $currentStatus
     * @return bool
     */
    public function trySetJobStatusAtomic($scheduleId, $newStatus, $currentStatus)
    {
        $connection = $this->getConnection();
        $result = $connection->update(
            $this->getTable('cron_schedule'),
            ['status' => $newStatus],
            ['schedule_id = ?' => $scheduleId, 'status = ?' => $currentStatus]
        );
        if ($result == 1) {
            return true;
        }
        return false;
    }

    /**
     * Sets schedule status only if no existing schedules with the same job code have that status.
     *
     * This is used to implement locking for cron jobs.
     * If the schedule is currently in $currentStatus and there are no existing
     * schedules with the same job code and $newStatus, set the schedule to
     * $newStatus and return true. Otherwise, return false.
     *
     * @param string $scheduleId
     * @param string $newStatus
     * @param string $currentStatus
     * @return bool
     * @since 100.2.0
     */
    public function trySetJobUniqueStatusAtomic($scheduleId, $newStatus, $currentStatus)
    {
        $connection = $this->getConnection();

        // this condition added to avoid cron jobs locking after incorrect termination of running job
        $match = $connection->quoteInto(
            'existing.job_code = current.job_code ' .
            'AND (existing.executed_at > UTC_TIMESTAMP() - INTERVAL 1 DAY OR existing.executed_at IS NULL) ' .
            'AND existing.status = ?',
            $newStatus
        );

        $selectIfUnlocked = $connection->select()
            ->joinLeft(
                ['existing' => $this->getTable('cron_schedule')],
                $match,
                ['status' => new \Zend_Db_Expr($connection->quote($newStatus))]
            )
            ->where('current.schedule_id = ?', $scheduleId)
            ->where('current.status = ?', $currentStatus)
            ->where('existing.schedule_id IS NULL');

        $update = $connection->updateFromSelect($selectIfUnlocked, ['current' => $this->getTable('cron_schedule')]);
        $result = $connection->query($update)->rowCount();

        if ($result == 1) {
            return true;
        }
        return false;
    }
}
