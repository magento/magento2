<?php
/**
 * Copyright Â© Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magemojo\Cron\Model;

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
    public function _construct() {
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
     * Sets schedule status only if no existing schedules with the same job code
     * have that status.  This is used to implement locking for cron jobs.
     *
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

        $match = $connection->select()
          ->from(['schedule' => $this->getTable('cron_schedule')])
          ->where('schedule.schedule_id = ?', $scheduleId);

        $result = $connection->fetchAll($match);

        $selectIfUnlocked = $connection->select()
          ->from(['schedule' => $this->getTable('cron_schedule')])
          ->where('schedule.job_code = ?', $result[0]["job_code"])
          ->where('schedule.executed_at > UTC_TIMESTAMP() - INTERVAL 1 DAY')
          ->where('schedule.status = ? ', $newStatus);
        $result = $connection->query($selectIfUnlocked)->rowCount();

        if ($result == 0) {
          $where = $connection->quoteInto('schedule_id =?', $scheduleId);
          $result = $connection->update(
            $this->getTable('cron_schedule'),
            array('status'=>$newStatus),
            $where);

          if ($result == 1) {
            return true;
          } else {
            return false;
          }
        }
        return false;
    }
}

