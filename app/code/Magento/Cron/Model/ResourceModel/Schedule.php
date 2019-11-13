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
     * Max retries for deadlocks
     */
    private const MAX_RETRIES = 5;

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
        $result = (int) $this->retry(
            function () use ($connection, $newStatus, $scheduleId, $currentStatus) {
                return $connection->update(
                    $this->getTable('cron_schedule'),
                    ['status' => $newStatus],
                    ['schedule_id = ?' => $scheduleId, 'status = ?' => $currentStatus]
                );
            }
        );

        return $result == 1;
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

        return $result == 1;
    }

    /**
     * @inheritdoc
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        return $this->retry(
            function () use ($object) {
                return parent::save($object);
            }
        );
    }

    /**
     * Clean up schedule
     *
     * @param mixed $where
     * @return int
     */
    public function cleanup($where = ''): int
    {
        $connection = $this->getConnection();

        return (int) $this->retry(
            function () use ($connection, $where) {
                return $connection->delete($this->getTable('cron_schedule'), $where);
            }
        );
    }

    /**
     * Sets new status for records in the schedule table by job code
     *
     * @param string $jobCode
     * @param string $newStatus
     * @param string $currentStatus
     * @return int
     */
    public function trySetJobStatuses(string $jobCode, string $newStatus, string $currentStatus): int
    {
        $connection = $this->getConnection();

        return (int) $this->retry(
            function () use ($connection, $jobCode, $newStatus, $currentStatus) {
                return $connection->update(
                    $this->getTable('cron_schedule'),
                    ['status' => $newStatus],
                    ['job_code = ?' => $jobCode, 'status = ?' => $currentStatus]
                );
            }
        );
    }

    /**
     * Retry deadlocks
     *
     * @param callable $callback
     * @return mixed
     */
    private function retry(callable $callback)
    {
        for ($retries = self::MAX_RETRIES - 1; $retries > 0; $retries--) {
            try {
                return $callback();
            } catch (\Magento\Framework\DB\Adapter\DeadlockException $e) {
                continue;
            }
        }

        return $callback();
    }
}
