<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Handling cron jobs
 */
namespace Magento\Cron\Observer;

use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Event\ObserverInterface;
use \Magento\Cron\Model\Schedule;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessCronQueueObserver implements ObserverInterface
{
    /**#@+
     * Cache key values
     */
    const CACHE_KEY_LAST_SCHEDULE_GENERATE_AT = 'cron_last_schedule_generate_at';

    const CACHE_KEY_LAST_HISTORY_CLEANUP_AT = 'cron_last_history_cleanup_at';

    /**
     * Flag for internal communication between processes for running
     * all jobs in a group in parallel as a separate process
     */
    const STANDALONE_PROCESS_STARTED = 'standaloneProcessStarted';

    /**#@-*/

    /**#@+
     * List of configurable constants used to calculate and validate during handling cron jobs
     */
    const XML_PATH_SCHEDULE_GENERATE_EVERY = 'schedule_generate_every';

    const XML_PATH_SCHEDULE_AHEAD_FOR = 'schedule_ahead_for';

    const XML_PATH_SCHEDULE_LIFETIME = 'schedule_lifetime';

    const XML_PATH_HISTORY_CLEANUP_EVERY = 'history_cleanup_every';

    const XML_PATH_HISTORY_SUCCESS = 'history_success_lifetime';

    const XML_PATH_HISTORY_FAILURE = 'history_failure_lifetime';

    /**#@-*/

    /**
     * Value of seconds in one minute
     */
    const SECONDS_IN_MINUTE = 60;

    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule\Collection
     */
    protected $pendingSchedules;

    /**
     * @var \Magento\Cron\Model\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ScheduleFactory
     */
    protected $scheduleFactory;

    /**
     * @var \Magento\Framework\App\Console\Request
     */
    protected $request;

    /**
     * @var \Magento\Framework\ShellInterface
     */
    protected $shell;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Symfony\Component\Process\PhpExecutableFinder
     */
    protected $phpExecutableFinder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var array
     */
    protected $invalid = [];

    /**
     * @var array
     */
    protected $jobs;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Cron\Model\ScheduleFactory $scheduleFactory
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Cron\Model\ConfigInterface $config
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Console\Request $request
     * @param \Magento\Framework\ShellInterface $shell
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\Process\PhpExecutableFinderFactory $phpExecutableFinderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\State $state
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Cron\Model\ScheduleFactory $scheduleFactory,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Cron\Model\ConfigInterface $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Console\Request $request,
        \Magento\Framework\ShellInterface $shell,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Process\PhpExecutableFinderFactory $phpExecutableFinderFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\State $state
    ) {
        $this->objectManager = $objectManager;
        $this->scheduleFactory = $scheduleFactory;
        $this->cache = $cache;
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->shell = $shell;
        $this->timezone = $timezone;
        $this->phpExecutableFinder = $phpExecutableFinderFactory->create();
        $this->logger = $logger;
        $this->state = $state;
    }

    /**
     * Process cron queue
     * Generate tasks schedule
     * Cleanup tasks schedule
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $pendingJobs = $this->getPendingSchedules();
        $currentTime = $this->timezone->scopeTimeStamp();
        $jobGroupsRoot = $this->config->getJobs();

        $phpPath = $this->phpExecutableFinder->find() ?: 'php';

        foreach ($jobGroupsRoot as $groupId => $jobsRoot) {
            $this->cleanup($groupId);
            $this->generate($groupId);
            if ($this->request->getParam('group') !== null
                && $this->request->getParam('group') !== '\'' . ($groupId) . '\''
                && $this->request->getParam('group') !== $groupId
            ) {
                continue;
            }
            if (($this->request->getParam(self::STANDALONE_PROCESS_STARTED) !== '1') && (
                    $this->scopeConfig->getValue(
                        'system/cron/' . $groupId . '/use_separate_process',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ) == 1
                )
            ) {
                $this->shell->execute(
                    $phpPath . ' %s cron:run --group=' . $groupId . ' --' . Cli::INPUT_KEY_BOOTSTRAP . '='
                    . self::STANDALONE_PROCESS_STARTED . '=1',
                    [
                        BP . '/bin/magento'
                    ]
                );
                continue;
            }

            /** @var \Magento\Cron\Model\Schedule $schedule */
            foreach ($pendingJobs as $schedule) {
                $jobConfig = isset($jobsRoot[$schedule->getJobCode()]) ? $jobsRoot[$schedule->getJobCode()] : null;
                if (!$jobConfig) {
                    continue;
                }

                $scheduledTime = strtotime($schedule->getScheduledAt());
                if ($scheduledTime > $currentTime) {
                    continue;
                }

                try {
                    if ($schedule->tryLockJob()) {
                        $this->runJob($scheduledTime, $currentTime, $jobConfig, $schedule, $groupId);
                    }
                } catch (\Exception $e) {
                    $schedule->setMessages($e->getMessage());
                    if ($schedule->getStatus() === Schedule::STATUS_ERROR) {
                        $this->logger->critical($e);
                    }
                    if ($schedule->getStatus() === Schedule::STATUS_MISSED
                        && $this->state->getMode() === State::MODE_DEVELOPER
                    ) {
                        $this->logger->info(
                            sprintf(
                                "%s Schedule Id: %s Job Code: %s",
                                $schedule->getMessages(),
                                $schedule->getScheduleId(),
                                $schedule->getJobCode()
                            )
                        );
                    }
                }
                $schedule->save();
            }
        }
    }

    /**
     * Execute job by calling specific class::method
     *
     * @param int $scheduledTime
     * @param int $currentTime
     * @param string[] $jobConfig
     * @param Schedule $schedule
     * @param string $groupId
     * @return void
     * @throws \Exception
     */
    public function runJob($scheduledTime, $currentTime, $jobConfig, $schedule, $groupId)
    {
        $scheduleLifetime = (int)$this->scopeConfig->getValue(
            'system/cron/' . $groupId . '/' . self::XML_PATH_SCHEDULE_LIFETIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $scheduleLifetime = $scheduleLifetime * self::SECONDS_IN_MINUTE;
        if ($scheduledTime < $currentTime - $scheduleLifetime) {
            $schedule->setStatus(Schedule::STATUS_MISSED);
            throw new \Exception('Too late for the schedule');
        }

        if (!isset($jobConfig['instance'], $jobConfig['method'])) {
            $schedule->setStatus(Schedule::STATUS_ERROR);
            throw new \Exception('No callbacks found');
        }
        $model = $this->objectManager->create($jobConfig['instance']);
        $callback = [$model, $jobConfig['method']];
        if (!is_callable($callback)) {
            $schedule->setStatus(Schedule::STATUS_ERROR);
            throw new \Exception(
                sprintf('Invalid callback: %s::%s can\'t be called', $jobConfig['instance'], $jobConfig['method'])
            );
        }

        $schedule->setExecutedAt(strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp()))->save();

        try {
            call_user_func_array($callback, [$schedule]);
        } catch (\Exception $e) {
            $schedule->setStatus(Schedule::STATUS_ERROR);
            throw $e;
        }

        $schedule->setStatus(Schedule::STATUS_SUCCESS)->setFinishedAt(strftime(
            '%Y-%m-%d %H:%M:%S',
            $this->timezone->scopeTimeStamp()
        ));
    }

    /**
     * Return job collection from data base with status 'pending'
     *
     * @return \Magento\Cron\Model\ResourceModel\Schedule\Collection
     */
    public function getPendingSchedules()
    {
        if (!$this->pendingSchedules) {
            $this->pendingSchedules = $this->scheduleFactory->create()->getCollection()->addFieldToFilter(
                'status',
                Schedule::STATUS_PENDING
            )->load();
        }
        return $this->pendingSchedules;
    }

    /**
     * Generate cron schedule
     *
     * @param string $groupId
     * @return $this
     */
    public function generate($groupId)
    {
        /**
         * check if schedule generation is needed
         */
        $lastRun = (int)$this->cache->load(self::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT . $groupId);
        $rawSchedulePeriod = (int)$this->scopeConfig->getValue(
            'system/cron/' . $groupId . '/' . self::XML_PATH_SCHEDULE_GENERATE_EVERY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $schedulePeriod = $rawSchedulePeriod * self::SECONDS_IN_MINUTE;
        if ($lastRun > $this->timezone->scopeTimeStamp() - $schedulePeriod) {
            return $this;
        }

        $schedules = $this->getPendingSchedules();
        $exists = [];
        /** @var Schedule $schedule */
        foreach ($schedules as $schedule) {
            $exists[$schedule->getJobCode() . '/' . $schedule->getScheduledAt()] = 1;
        }

        /**
         * generate global crontab jobs
         */
        $jobs = $this->getJobs();
        $this->invalid = [];
        $this->generateJobs($jobs[$groupId], $exists, $groupId);
        $this->cleanupScheduleMismatches();

        /**
         * save time schedules generation was ran with no expiration
         */
        $this->cache->save(
            $this->timezone->scopeTimeStamp(),
            self::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT . $groupId,
            ['crontab'],
            null
        );

        return $this;
    }

    /**
     * Generate jobs for config information
     *
     * @param   array $jobs
     * @param   array $exists
     * @param   string $groupId
     * @return  $this
     */
    private function generateJobs($jobs, $exists, $groupId)
    {
        foreach ($jobs as $jobCode => $jobConfig) {
            $cronExpression = $this->getCronExpression($jobConfig);
            if (!$cronExpression) {
                continue;
            }

            $timeInterval = $this->getScheduleTimeInterval($groupId);
            $this->saveSchedule($jobCode, $cronExpression, $timeInterval, $exists);
        }
        return $this;
    }

    /**
     * Clean expired jobs
     *
     * @param string $groupId
     * @return $this
     */
    private function cleanup($groupId)
    {
        $this->cleanupDisabledJobs($groupId);

        // check if history cleanup is needed
        $lastCleanup = (int)$this->cache->load(self::CACHE_KEY_LAST_HISTORY_CLEANUP_AT . $groupId);
        $historyCleanUp = (int)$this->scopeConfig->getValue(
            'system/cron/' . $groupId . '/' . self::XML_PATH_HISTORY_CLEANUP_EVERY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($lastCleanup > $this->timezone->scopeTimeStamp() - $historyCleanUp * self::SECONDS_IN_MINUTE) {
            return $this;
        }

        // check how long the record should stay unprocessed before marked as MISSED
        $scheduleLifetime = (int)$this->scopeConfig->getValue(
            'system/cron/' . $groupId . '/' . self::XML_PATH_SCHEDULE_LIFETIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $scheduleLifetime = $scheduleLifetime * self::SECONDS_IN_MINUTE;

        /**
         * @var \Magento\Cron\Model\ResourceModel\Schedule\Collection $history
         */
        $history = $this->scheduleFactory->create()->getCollection()->addFieldToFilter(
            'status',
            ['in' => [Schedule::STATUS_SUCCESS, Schedule::STATUS_MISSED, Schedule::STATUS_ERROR]]
        )->load();

        $historySuccess = (int)$this->scopeConfig->getValue(
            'system/cron/' . $groupId . '/' . self::XML_PATH_HISTORY_SUCCESS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $historyFailure = (int)$this->scopeConfig->getValue(
            'system/cron/' . $groupId . '/' . self::XML_PATH_HISTORY_FAILURE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $historyLifetimes = [
            Schedule::STATUS_SUCCESS => $historySuccess * self::SECONDS_IN_MINUTE,
            Schedule::STATUS_MISSED => $historyFailure * self::SECONDS_IN_MINUTE,
            Schedule::STATUS_ERROR => $historyFailure * self::SECONDS_IN_MINUTE,
        ];

        $now = $this->timezone->scopeTimeStamp();
        /** @var Schedule $record */
        foreach ($history as $record) {
            $checkTime = $record->getExecutedAt() ? strtotime($record->getExecutedAt()) :
                strtotime($record->getScheduledAt()) + $scheduleLifetime;
            if ($checkTime < $now - $historyLifetimes[$record->getStatus()]) {
                $record->delete();
            }
        }

        // save time history cleanup was ran with no expiration
        $this->cache->save(
            $this->timezone->scopeTimeStamp(),
            self::CACHE_KEY_LAST_HISTORY_CLEANUP_AT . $groupId,
            ['crontab'],
            null
        );

        return $this;
    }

    /**
     * @param array $jobConfig
     * @return mixed
     */
    private function getConfigSchedule($jobConfig)
    {
        $cronExpr = $this->scopeConfig->getValue(
            $jobConfig['config_path'],
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $cronExpr;
    }

    /**
     * @param string $jobCode
     * @param string $cronExpression
     * @param int $timeInterval
     * @param array $exists
     * @return void
     */
    private function saveSchedule($jobCode, $cronExpression, $timeInterval, $exists)
    {
        $currentTime = $this->timezone->scopeTimeStamp();
        $timeAhead = $currentTime + $timeInterval;
        for ($time = $currentTime; $time < $timeAhead; $time += self::SECONDS_IN_MINUTE) {
            $scheduledAt = strftime('%Y-%m-%d %H:%M:00', $time);
            $alreadyScheduled = !empty($exists[$jobCode . '/' . $scheduledAt]);
            $schedule = $this->generateSchedule($jobCode, $cronExpression, $time);
            $valid = $schedule->trySchedule();
            if (!$valid) {
                if ($alreadyScheduled) {
                    if (!isset($this->invalid[$jobCode])) {
                        $this->invalid[$jobCode] = [];
                    }
                    $this->invalid[$jobCode][] = $scheduledAt;
                }
                continue;
            }
            if (!$alreadyScheduled) {
                // time matches cron expression
                $schedule->save();
            }
        }
    }

    /**
     * @param string $jobCode
     * @param string $cronExpression
     * @param int $time
     * @return Schedule
     */
    private function generateSchedule($jobCode, $cronExpression, $time)
    {
        $schedule = $this->scheduleFactory->create()
            ->setCronExpr($cronExpression)
            ->setJobCode($jobCode)
            ->setStatus(Schedule::STATUS_PENDING)
            ->setCreatedAt(strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp()))
            ->setScheduledAt(strftime('%Y-%m-%d %H:%M', $time));

        return $schedule;
    }

    /**
     * @param string $groupId
     * @return int
     */
    private function getScheduleTimeInterval($groupId)
    {
        $scheduleAheadFor = (int)$this->scopeConfig->getValue(
            'system/cron/' . $groupId . '/' . self::XML_PATH_SCHEDULE_AHEAD_FOR,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $scheduleAheadFor = $scheduleAheadFor * self::SECONDS_IN_MINUTE;

        return $scheduleAheadFor;
    }

    /**
     * Clean up scheduled jobs that are disabled in the configuration
     * This can happen when you turn off a cron job in the config and flush the cache
     *
     * @param string $groupId
     * @return void
     */
    public function cleanupDisabledJobs($groupId)
    {
        $jobs = $this->getJobs();
        foreach ($jobs[$groupId] as $jobCode => $jobConfig) {
            if (!$this->getCronExpression($jobConfig)) {
                /** @var \Magento\Cron\Model\ResourceModel\Schedule $scheduleResource */
                $scheduleResource = $this->scheduleFactory->create()->getResource();
                $scheduleResource->getConnection()->delete($scheduleResource->getMainTable(), [
                    'status=?' => Schedule::STATUS_PENDING,
                    'job_code=?' => $jobCode,
                ]);
            }
        }
    }

    /**
     * @param array $jobConfig
     * @return null|string
     */
    private function getCronExpression($jobConfig)
    {
        $cronExpression = null;
        if (isset($jobConfig['config_path'])) {
            $cronExpression = $this->getConfigSchedule($jobConfig) ?: null;
        }

        if (!$cronExpression) {
            if (isset($jobConfig['schedule'])) {
                $cronExpression = $jobConfig['schedule'];
            }
        }
        return $cronExpression;
    }

    /**
     * Clean up scheduled jobs that do not match their cron expression anymore
     * This can happen when you change the cron expression and flush the cache
     *
     * @return $this
     */
    public function cleanupScheduleMismatches()
    {
        foreach ($this->invalid as $jobCode => $scheduledAtList) {
            /** @var \Magento\Cron\Model\ResourceModel\Schedule $scheduleResource */
            $scheduleResource = $this->scheduleFactory->create()->getResource();
            $scheduleResource->getConnection()->delete($scheduleResource->getMainTable(), [
                'status=?' => Schedule::STATUS_PENDING,
                'job_code=?' => $jobCode,
                'scheduled_at in (?)' => $scheduledAtList,
            ]);
        }
        return $this;
    }

    /**
     * @return array
     */
    private function getJobs()
    {
        if ($this->jobs === null) {
            $this->jobs = $this->config->getJobs();
        }
        return $this->jobs;
    }
}
