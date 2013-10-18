<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Cron
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Handling cron jobs
 */
namespace Magento\Cron\Model;

class Observer
{
    /**#@+
     * Cache key values
     */
    const CACHE_KEY_LAST_SCHEDULE_GENERATE_AT   = 'cron_last_schedule_generate_at';
    const CACHE_KEY_LAST_HISTORY_CLEANUP_AT     = 'cron_last_history_cleanup_at';
    /**#@-*/

    /**#@+
     * List of configurable constants used to calculate and validate during handling cron jobs
     */
    const XML_PATH_SCHEDULE_GENERATE_EVERY  = 'system/cron/schedule_generate_every';
    const XML_PATH_SCHEDULE_AHEAD_FOR       = 'system/cron/schedule_ahead_for';
    const XML_PATH_SCHEDULE_LIFETIME        = 'system/cron/schedule_lifetime';
    const XML_PATH_HISTORY_CLEANUP_EVERY    = 'system/cron/history_cleanup_every';
    const XML_PATH_HISTORY_SUCCESS          = 'system/cron/history_success_lifetime';
    const XML_PATH_HISTORY_FAILURE          = 'system/cron/history_failure_lifetime';
    /**#@-*/

    /**
     * Value of seconds in one minute
     */
    const SECONDS_IN_MINUTE = 60;

    /** @var \Magento\Cron\Model\Resource\Schedule\Collection */
    protected $_pendingSchedules;

    /** @var \Magento\Cron\Model\ConfigInterface */
    protected $_config;

    /** @var \Magento\Core\Model\ObjectManager */
    protected $_objectManager;

    /** @var \Magento\Core\Model\App */
    protected $_app;

    /** @var \Magento\Core\Model\Store\Config */
    protected $_coreStoreConfig;
    
    /**
     * Initialize parameters
     *
     * @param \Magento\ObjectManager              $objectManager
     * @param \Magento\Core\Model\AppInterface    $app
     * @param \Magento\Cron\Model\ConfigInterface $config
     * @param \Magento\Core\Model\Store\Config    $coreStoreConfig
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        \Magento\Core\Model\AppInterface $app,
        \Magento\Cron\Model\ConfigInterface $config,
        \Magento\Core\Model\Store\Config $coreStoreConfig
    ) {
        $this->_objectManager = $objectManager;
        $this->_app = $app;
        $this->_config = $config;
        $this->_coreStoreConfig = $coreStoreConfig;
    }

    /**
     * Process cron queue
     * Generate tasks schedule
     * Cleanup tasks schedule
     *
     * @param \Magento\Event\Observer $observer
     */
    public function dispatch($observer)
    {
        $pendingJobs = $this->_getPendingSchedules();
        $currentTime = time();
        $jobsRoot = $this->_config->getJobs();

        /** @var $schedule \Magento\Cron\Model\Schedule */
        foreach ($pendingJobs as $schedule) {
            $jobConfig = isset($jobsRoot[$schedule->getJobCode()]) ? $jobsRoot[$schedule->getJobCode()] : null;
            if (!$jobConfig) {
                continue;
            }

            $scheduledTime = strtotime($schedule->getScheduledAt());
            if ($scheduledTime > $currentTime || !$schedule->tryLockJob()) {
                continue;
            }

            try {
                $this->_runJob($scheduledTime, $currentTime, $jobConfig, $schedule);
            } catch (\Exception $e) {
                $schedule->setMessages($e->getMessage());
            }
            $schedule->save();
        }

        $this->_generate();
        $this->_cleanup();
    }

    /**
     * Execute job by calling specific class::method
     *
     * @param $scheduledTime
     * @param $currentTime
     * @param $jobConfig
     * @param \Magento\Cron\Model\Schedule $schedule
     *
     * @throws \Exception
     */
    protected function _runJob($scheduledTime, $currentTime, $jobConfig, $schedule)
    {
        $scheduleLifetime = (int)$this->_coreStoreConfig->getConfig(self::XML_PATH_SCHEDULE_LIFETIME, 'default');
        $scheduleLifetime = $scheduleLifetime * self::SECONDS_IN_MINUTE;
        if ($scheduledTime < $currentTime - $scheduleLifetime) {
            $schedule->setStatus(\Magento\Cron\Model\Schedule::STATUS_MISSED);
            throw new \Exception('Too late for the schedule');
        }

        if (!isset($jobConfig['instance'], $jobConfig['method'])) {
            $schedule->setStatus(\Magento\Cron\Model\Schedule::STATUS_ERROR);
            throw new \Exception('No callbacks found');
        }
        $model = $this->_objectManager->create($jobConfig['instance']);
        $callback = array($model, $jobConfig['method']);
        if (!is_callable($callback)) {
            $schedule->setStatus(\Magento\Cron\Model\Schedule::STATUS_ERROR);
            throw new \Exception(
                sprintf('Invalid callback: %s::%s can\'t be called', $jobConfig['instance'], $jobConfig['method'])
            );
        }

        /**
         * though running status is set in tryLockJob we must set it here because the object
         * was loaded with a pending status and will set it back to pending if we don't set it here
         */
        $schedule
            ->setStatus(\Magento\Cron\Model\Schedule::STATUS_RUNNING)
            ->setExecutedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
            ->save();

        call_user_func_array($callback, array($schedule));

        $schedule
            ->setStatus(\Magento\Cron\Model\Schedule::STATUS_SUCCESS)
            ->setFinishedAt(strftime('%Y-%m-%d %H:%M:%S', time()));
    }

    /**
     * Return job collection from data base with status 'pending'
     *
     * @return \Magento\Cron\Model\Resource\Schedule\Collection
     */
    protected function _getPendingSchedules()
    {
        if (!$this->_pendingSchedules) {
            $this->_pendingSchedules = $this->_objectManager->create('Magento\Cron\Model\Schedule')->getCollection()
                ->addFieldToFilter('status', \Magento\Cron\Model\Schedule::STATUS_PENDING)
                ->load();
        }
        return $this->_pendingSchedules;
    }

    /**
     * Generate cron schedule
     *
     * @return \Magento\Cron\Model\Observer
     */
    protected function _generate()
    {
        /**
         * check if schedule generation is needed
         */
        $lastRun = (int)$this->_app->loadCache(self::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT);
        $rawSchedulePeriod = (int)$this->_coreStoreConfig->getConfig(self::XML_PATH_SCHEDULE_GENERATE_EVERY, 'default');
        $schedulePeriod = $rawSchedulePeriod * self::SECONDS_IN_MINUTE;
        if ($lastRun > time() - $schedulePeriod) {
            return $this;
        }

        $schedules = $this->_getPendingSchedules();
        $exists = array();
        /** @var \Magento\Cron\Model\Schedule $schedule */
        foreach ($schedules as $schedule) {
            $exists[$schedule->getJobCode() . '/' . $schedule->getScheduledAt()] = 1;
        }

        /**
         * generate global crontab jobs
         */
        $jobs = $this->_config->getJobs();
        $this->_generateJobs($jobs, $exists);

        /**
         * save time schedules generation was ran with no expiration
         */
        $this->_app->saveCache(time(), self::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT, array('crontab'), null);

        return $this;
    }

    /**
     * Generate jobs for config information
     *
     * @param   $jobs
     * @param   array $exists
     * @return  \Magento\Cron\Model\Observer
     */
    protected function _generateJobs($jobs, $exists)
    {
        $scheduleAheadFor = (int)$this->_coreStoreConfig->getConfig(self::XML_PATH_SCHEDULE_AHEAD_FOR, 'default');
        $scheduleAheadFor = $scheduleAheadFor * self::SECONDS_IN_MINUTE;
        /** @var \Magento\Cron\Model\Schedule $schedule */
        $schedule = $this->_objectManager->create('Magento\Cron\Model\Schedule');

        foreach ($jobs as $jobCode => $jobConfig) {
            $cronExpr = null;
            if (isset($jobConfig['config_path'])) {
                $cronExpr = $this->_coreStoreConfig->getConfig($jobConfig['config_path'], 'default');
            } elseif (empty($cronExpr) && isset($jobConfig['schedule'])) {
                $cronExpr = $jobConfig['schedule'];
            }

            if (!$cronExpr) {
                continue;
            }

            $currentTime = time();
            $timeAhead = $currentTime + $scheduleAheadFor;
            $schedule->setJobCode($jobCode)
                ->setCronExpr($cronExpr)
                ->setStatus(\Magento\Cron\Model\Schedule::STATUS_PENDING);

            for ($time = $currentTime; $time < $timeAhead; $time += self::SECONDS_IN_MINUTE) {
                $ts = strftime('%Y-%m-%d %H:%M:00', $time);
                if (!empty($exists[$jobCode . '/' . $ts])) {
                    // already scheduled
                    continue;
                }
                if (!$schedule->trySchedule($time)) {
                    // time does not match cron expression
                    continue;
                }
                $schedule->unsScheduleId()->save();
            }
        }
        return $this;
    }

    /**
     * Clean existed jobs
     *
     * @return $this
     */
    protected function _cleanup()
    {
        // check if history cleanup is needed
        $lastCleanup = (int)$this->_app->loadCache(self::CACHE_KEY_LAST_HISTORY_CLEANUP_AT);
        $historyCleanUp = (int)$this->_coreStoreConfig->getConfig(self::XML_PATH_HISTORY_CLEANUP_EVERY, 'default');
        if ($lastCleanup > time() - $historyCleanUp * self::SECONDS_IN_MINUTE) {
            return $this;
        }

        /** @var \Magento\Cron\Model\Resource\Schedule\Collection $history */
        $history = $this->_objectManager->create('Magento\Cron\Model\Schedule')->getCollection()
            ->addFieldToFilter('status', array('in' => array(
                \Magento\Cron\Model\Schedule::STATUS_SUCCESS,
                \Magento\Cron\Model\Schedule::STATUS_MISSED,
                \Magento\Cron\Model\Schedule::STATUS_ERROR,
            )))->load();

        $historySuccess = (int)$this->_coreStoreConfig->getConfig(self::XML_PATH_HISTORY_SUCCESS, 'default');
        $historyFailure = (int)$this->_coreStoreConfig->getConfig(self::XML_PATH_HISTORY_FAILURE, 'default');
        $historyLifetimes = array(
            \Magento\Cron\Model\Schedule::STATUS_SUCCESS => $historySuccess * self::SECONDS_IN_MINUTE,
            \Magento\Cron\Model\Schedule::STATUS_MISSED => $historyFailure * self::SECONDS_IN_MINUTE,
            \Magento\Cron\Model\Schedule::STATUS_ERROR => $historyFailure * self::SECONDS_IN_MINUTE,
        );

        $now = time();
        /** @var \Magento\Cron\Model\Schedule $record */
        foreach ($history as $record) {
            if (strtotime($record->getExecutedAt()) < $now - $historyLifetimes[$record->getStatus()]) {
                $record->delete();
            }
        }

        // save time history cleanup was ran with no expiration
        $this->_app->saveCache(time(), self::CACHE_KEY_LAST_HISTORY_CLEANUP_AT, array('crontab'), null);

        return $this;
    }
}
