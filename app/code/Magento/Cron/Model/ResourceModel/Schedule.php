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
        $connection->beginTransaction();

        // this condition added to avoid cron jobs locking after incorrect termination of running job
        $match = $connection->quoteInto(
            'existing.job_code = current.job_code ' .
            'AND existing.status = ? ' .
            'AND (existing.executed_at > UTC_TIMESTAMP() - INTERVAL 1 DAY OR existing.executed_at IS NULL)',
            $newStatus
        );

        // Select and lock all related schedules - this prevents deadlock in case cron overlaps and two jobs of
        // the same code attempt to lock at the same time, and force them to serialize
        $selectIfUnlocked = $connection->select()
            ->from(
                ['current' => $this->getTable('cron_schedule')],
                []
            )
            ->joinLeft(
                ['existing' => $this->getTable('cron_schedule')],
                $match,
                ['existing.schedule_id']
            )
            ->where('current.schedule_id = ?', $scheduleId)
            ->where('current.status = ?', $currentStatus)
            ->forUpdate(true);

        $scheduleId = $connection->fetchOne($selectIfUnlocked);
        if (!empty($scheduleId)) {
            // Existing running schedule found
            $connection->commit();
            return false;
        }

        // Mark our schedule as running
        $connection->update(
            $this->getTable('cron_schedule'),
            ['status' => new \Zend_Db_Expr($connection->quote($newStatus))],
            ['schedule_id = ?' => $scheduleId]
        );

        $connection->commit();
        return true;
    }
}
