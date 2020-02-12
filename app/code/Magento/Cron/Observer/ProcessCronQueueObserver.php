<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Handling cron jobs
 */
namespace Magento\Cron\Observer;

use Magento\Cron\Model\Schedule;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Profiler\Driver\Standard\Stat;
use Magento\Framework\Profiler\Driver\Standard\StatFactory;
use Magento\Cron\Model\DeadlockRetrierInterface;

/**
 * The observer for processing cron jobs.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
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
     * How long to wait for cron group to become unlocked
     */
    const LOCK_TIMEOUT = 60;

    /**
     * Static lock prefix for cron group locking
     */
    const LOCK_PREFIX = 'CRON_';

    /**
     * Max retries for acquire locks for cron jobs
     */
    const MAX_RETRIES = 5;

    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule\Collection
     */
    protected $_pendingSchedules;

    /**
     * @var \Magento\Cron\Model\ConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ScheduleFactory
     */
    protected $_scheduleFactory;

    /**
     * @var \Magento\Framework\App\Console\Request
     */
    protected $_request;

    /**
     * @var \Magento\Framework\ShellInterface
     */
    protected $_shell;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Symfony\Component\Process\PhpExecutableFinder
     */
    protected $phpExecutableFinder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Magento\Framework\Lock\LockManagerInterface
     */
    private $lockManager;

    /**
     * @var array
     */
    private $invalid = [];

    /**
     * @var Stat
     */
    private $statProfiler;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var DeadlockRetrierInterface
     */
    private $retrier;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Cron\Model\ScheduleFactory $scheduleFactory
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Cron\Model\ConfigInterface $config
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Console\Request $request
     * @param \Magento\Framework\ShellInterface $shell
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Process\PhpExecutableFinderFactory $phpExecutableFinderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param State $state
     * @param StatFactory $statFactory
     * @param \Magento\Framework\Lock\LockManagerInterface $lockManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param DeadlockRetrierInterface $retrier
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
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Process\PhpExecutableFinderFactory $phpExecutableFinderFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\State $state,
        StatFactory $statFactory,
        \Magento\Framework\Lock\LockManagerInterface $lockManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        DeadlockRetrierInterface $retrier
    ) {
        $this->_objectManager = $objectManager;
        $this->_scheduleFactory = $scheduleFactory;
        $this->_cache = $cache;
        $this->_config = $config;
        $this->_scopeConfig = $scopeConfig;
        $this->_request = $request;
        $this->_shell = $shell;
        $this->dateTime = $dateTime;
        $this->phpExecutableFinder = $phpExecutableFinderFactory->create();
        $this->logger = $logger;
        $this->state = $state;
        $this->statProfiler = $statFactory->create();
        $this->lockManager = $lockManager;
        $this->eventManager = $eventManager;
        $this->retrier = $retrier;
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
        $currentTime = $this->dateTime->gmtTimestamp();
        $jobGroupsRoot = $this->_config->getJobs();
        // sort jobs groups to start from used in separated process
        uksort(
            $jobGroupsRoot,
            function ($a, $b) {
                return $this->getCronGroupConfigurationValue($b, 'use_separate_process')
                    - $this->getCronGroupConfigurationValue($a, 'use_separate_process');
            }
        );

        $phpPath = $this->phpExecutableFinder->find() ?: 'php';

        foreach ($jobGroupsRoot as $groupId => $jobsRoot) {
            if (!$this->isGroupInFilter($groupId)) {
                continue;
            }
            if ($this->_request->getParam(self::STANDALONE_PROCESS_STARTED) !== '1'
                && $this->getCronGroupConfigurationValue($groupId, 'use_separate_process') == 1
            ) {
                $this->_shell->execute(
                    $phpPath . ' %s cron:run --group=' . $groupId . ' --' . Cli::INPUT_KEY_BOOTSTRAP . '='
                    . self::STANDALONE_PROCESS_STARTED . '=1',
                    [
                        BP . '/bin/magento'
                    ]
                );
                continue;
            }

            $this->lockGroup(
                $groupId,
                function ($groupId) use ($currentTime) {
                    $this->cleanupJobs($groupId, $currentTime);
                    $this->generateSchedules($groupId);
                }
            );
            $this->processPendingJobs($groupId, $jobsRoot, $currentTime);
        }
    }

    /**
     * Lock group
     *
     * It should be taken by standalone (child) process, not by the parent process.
     *
     * @param int $groupId
     * @param callable $callback
     *
     * @return void
     */
    private function lockGroup($groupId, callable $callback)
    {
        if (!$this->lockManager->lock(self::LOCK_PREFIX . $groupId, self::LOCK_TIMEOUT)) {
            $this->logger->warning(
                sprintf(
                    "Could not acquire lock for cron group: %s, skipping run",
                    $groupId
                )
            );
            return;
        }
        try {
            $callback($groupId);
        } finally {
            $this->lockManager->unlock(self::LOCK_PREFIX . $groupId);
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
    protected function _runJob($scheduledTime, $currentTime, $jobConfig, $schedule, $groupId)
    {
        $jobCode = $schedule->getJobCode();
        $scheduleLifetime = $this->getCronGroupConfigurationValue($groupId, self::XML_PATH_SCHEDULE_LIFETIME);
        $scheduleLifetime = $scheduleLifetime * self::SECONDS_IN_MINUTE;
        if ($scheduledTime < $currentTime - $scheduleLifetime) {
            $schedule->setStatus(Schedule::STATUS_MISSED);
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception(sprintf('Cron Job %s is missed at %s', $jobCode, $schedule->getScheduledAt()));
        }

        if (!isset($jobConfig['instance'], $jobConfig['method'])) {
            $schedule->setStatus(Schedule::STATUS_ERROR);
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception(sprintf('No callbacks found for cron job %s', $jobCode));
        }
        $model = $this->_objectManager->create($jobConfig['instance']);
        $callback = [$model, $jobConfig['method']];
        if (!is_callable($callback)) {
            $schedule->setStatus(Schedule::STATUS_ERROR);
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception(
                sprintf('Invalid callback: %s::%s can\'t be called', $jobConfig['instance'], $jobConfig['method'])
            );
        }

        $schedule->setExecutedAt(strftime('%Y-%m-%d %H:%M:%S', $this->dateTime->gmtTimestamp()));
        $this->retrier->execute(
            function () use ($schedule) {
                $schedule->save();
            },
            $schedule->getResource()->getConnection()
        );

        $this->startProfiling();
        $this->eventManager->dispatch('cron_job_run', ['job_name' => 'cron/' . $groupId . '/' . $jobCode]);

        try {
            $this->logger->info(sprintf('Cron Job %s is run', $jobCode));
            //phpcs:ignore Magento2.Functions.DiscouragedFunction
            call_user_func_array($callback, [$schedule]);
        } catch (\Throwable $e) {
            $schedule->setStatus(Schedule::STATUS_ERROR);
            $this->logger->error(
                sprintf(
                    'Cron Job %s has an error: %s. Statistics: %s',
                    $jobCode,
                    $e->getMessage(),
                    $this->getProfilingStat()
                )
            );
            if (!$e instanceof \Exception) {
                $e = new \RuntimeException(
                    'Error when running a cron job',
                    0,
                    $e
                );
            }
            throw $e;
        } finally {
            $this->stopProfiling();
        }

        $schedule->setStatus(
            Schedule::STATUS_SUCCESS
        )->setFinishedAt(
            strftime(
                '%Y-%m-%d %H:%M:%S',
                $this->dateTime->gmtTimestamp()
            )
        );

        $this->logger->info(
            sprintf(
                'Cron Job %s is successfully finished. Statistics: %s',
                $jobCode,
                $this->getProfilingStat()
            )
        );
    }

    /**
     * Starts profiling
     *
     * @return void
     */
    private function startProfiling()
    {
        $this->statProfiler->clear();
        $this->statProfiler->start('job', microtime(true), memory_get_usage(true), memory_get_usage());
    }

    /**
     * Stops profiling
     *
     * @return void
     */
    private function stopProfiling()
    {
        $this->statProfiler->stop('job', microtime(true), memory_get_usage(true), memory_get_usage());
    }

    /**
     * Retrieves statistics in the JSON format
     *
     * @return string
     */
    private function getProfilingStat()
    {
        $stat = $this->statProfiler->get('job');
        unset($stat[Stat::START]);
        return json_encode($stat);
    }

    /**
     * Return job collection from data base with status 'pending'.
     *
     * @param string $groupId
     * @return \Magento\Cron\Model\ResourceModel\Schedule\Collection
     */
    private function getPendingSchedules($groupId)
    {
        $jobs = $this->_config->getJobs();
        $pendingJobs = $this->_scheduleFactory->create()->getCollection();
        $pendingJobs->addFieldToFilter('status', Schedule::STATUS_PENDING);
        $pendingJobs->addFieldToFilter('job_code', ['in' => array_keys($jobs[$groupId])]);
        return $pendingJobs;
    }

    /**
     * Generate cron schedule
     *
     * @param string $groupId
     * @return $this
     */
    private function generateSchedules($groupId)
    {
        /**
         * check if schedule generation is needed
         */
        $lastRun = (int)$this->_cache->load(self::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT . $groupId);
        $rawSchedulePeriod = (int)$this->getCronGroupConfigurationValue(
            $groupId,
            self::XML_PATH_SCHEDULE_GENERATE_EVERY
        );
        $schedulePeriod = $rawSchedulePeriod * self::SECONDS_IN_MINUTE;
        if ($lastRun > $this->dateTime->gmtTimestamp() - $schedulePeriod) {
            return $this;
        }

        /**
         * save time schedules generation was ran with no expiration
         */
        $this->_cache->save(
            $this->dateTime->gmtTimestamp(),
            self::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT . $groupId,
            ['crontab'],
            null
        );

        $schedules = $this->getPendingSchedules($groupId);
        $exists = [];
        /** @var Schedule $schedule */
        foreach ($schedules as $schedule) {
            $exists[$schedule->getJobCode() . '/' . $schedule->getScheduledAt()] = 1;
        }

        /**
         * generate global crontab jobs
         */
        $jobs = $this->_config->getJobs();
        $this->invalid = [];
        $this->_generateJobs($jobs[$groupId], $exists, $groupId);
        $this->cleanupScheduleMismatches();

        return $this;
    }

    /**
     * Generate jobs for config information
     *
     * @param   array $jobs
     * @param   array $exists
     * @param   string $groupId
     * @return  void
     */
    protected function _generateJobs($jobs, $exists, $groupId)
    {
        foreach ($jobs as $jobCode => $jobConfig) {
            $cronExpression = $this->getCronExpression($jobConfig);
            if (!$cronExpression) {
                continue;
            }

            $timeInterval = $this->getScheduleTimeInterval($groupId);
            $this->saveSchedule($jobCode, $cronExpression, $timeInterval, $exists);
        }
    }

    /**
     * Clean expired jobs
     *
     * @param string $groupId
     * @param int $currentTime
     * @return void
     */
    private function cleanupJobs($groupId, $currentTime)
    {
        // check if history cleanup is needed
        $lastCleanup = (int)$this->_cache->load(self::CACHE_KEY_LAST_HISTORY_CLEANUP_AT . $groupId);
        $historyCleanUp = (int)$this->getCronGroupConfigurationValue($groupId, self::XML_PATH_HISTORY_CLEANUP_EVERY);
        if ($lastCleanup > $this->dateTime->gmtTimestamp() - $historyCleanUp * self::SECONDS_IN_MINUTE) {
            return $this;
        }
        // save time history cleanup was ran with no expiration
        $this->_cache->save(
            $this->dateTime->gmtTimestamp(),
            self::CACHE_KEY_LAST_HISTORY_CLEANUP_AT . $groupId,
            ['crontab'],
            null
        );

        $this->cleanupDisabledJobs($groupId);

        $historySuccess = (int)$this->getCronGroupConfigurationValue($groupId, self::XML_PATH_HISTORY_SUCCESS);
        $historyFailure = (int)$this->getCronGroupConfigurationValue($groupId, self::XML_PATH_HISTORY_FAILURE);
        $historyLifetimes = [
            Schedule::STATUS_SUCCESS => $historySuccess * self::SECONDS_IN_MINUTE,
            Schedule::STATUS_MISSED => $historyFailure * self::SECONDS_IN_MINUTE,
            Schedule::STATUS_ERROR => $historyFailure * self::SECONDS_IN_MINUTE,
            Schedule::STATUS_PENDING => max($historyFailure, $historySuccess) * self::SECONDS_IN_MINUTE,
        ];

        $jobs = $this->_config->getJobs()[$groupId];
        $count = 0;
        foreach ($historyLifetimes as $status => $time) {
            $count += $this->cleanup(
                [
                    'status = ?' => $status,
                    'job_code in (?)' => array_keys($jobs),
                    'created_at < ?' => $this->_scheduleFactory
                        ->create()
                        ->getResource()
                        ->getConnection()
                        ->formatDate($currentTime - $time)
                ]
            );
        }

        if ($count) {
            $this->logger->info(sprintf('%d cron jobs were cleaned', $count));
        }
    }

    /**
     * Get config of schedule.
     *
     * @param array $jobConfig
     * @return mixed
     */
    protected function getConfigSchedule($jobConfig)
    {
        $cronExpr = $this->_scopeConfig->getValue(
            $jobConfig['config_path'],
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $cronExpr;
    }

    /**
     * Save a schedule of cron job.
     *
     * @param string $jobCode
     * @param string $cronExpression
     * @param int $timeInterval
     * @param array $exists
     * @return void
     */
    protected function saveSchedule($jobCode, $cronExpression, $timeInterval, $exists)
    {
        $currentTime = $this->dateTime->gmtTimestamp();
        $timeAhead = $currentTime + $timeInterval;
        for ($time = $currentTime; $time < $timeAhead; $time += self::SECONDS_IN_MINUTE) {
            $scheduledAt = strftime('%Y-%m-%d %H:%M:00', $time);
            $alreadyScheduled = !empty($exists[$jobCode . '/' . $scheduledAt]);
            $schedule = $this->createSchedule($jobCode, $cronExpression, $time);
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
     * Create a schedule of cron job.
     *
     * @param string $jobCode
     * @param string $cronExpression
     * @param int $time
     * @return Schedule
     */
    protected function createSchedule($jobCode, $cronExpression, $time)
    {
        $schedule = $this->_scheduleFactory->create()
            ->setCronExpr($cronExpression)
            ->setJobCode($jobCode)
            ->setStatus(Schedule::STATUS_PENDING)
            ->setCreatedAt(strftime('%Y-%m-%d %H:%M:%S', $this->dateTime->gmtTimestamp()))
            ->setScheduledAt(strftime('%Y-%m-%d %H:%M', $time));

        return $schedule;
    }

    /**
     * Get time interval for scheduling.
     *
     * @param string $groupId
     * @return int
     */
    protected function getScheduleTimeInterval($groupId)
    {
        $scheduleAheadFor = (int)$this->getCronGroupConfigurationValue($groupId, self::XML_PATH_SCHEDULE_AHEAD_FOR);
        $scheduleAheadFor = $scheduleAheadFor * self::SECONDS_IN_MINUTE;

        return $scheduleAheadFor;
    }

    /**
     * Clean up scheduled jobs that are disabled in the configuration.
     *
     * This can happen when you turn off a cron job in the config and flush the cache.
     *
     * @param string $groupId
     * @return void
     */
    private function cleanupDisabledJobs($groupId)
    {
        $jobs = $this->_config->getJobs();
        $jobsToCleanup = [];
        foreach ($jobs[$groupId] as $jobCode => $jobConfig) {
            if (!$this->getCronExpression($jobConfig)) {
                /** @var \Magento\Cron\Model\ResourceModel\Schedule $scheduleResource */
                $jobsToCleanup[] = $jobCode;
            }
        }

        if (count($jobsToCleanup) > 0) {
            $count = $this->cleanup(
                [
                    'status = ?' => Schedule::STATUS_PENDING,
                    'job_code in (?)' => $jobsToCleanup,
                ]
            );

            $this->logger->info(sprintf('%d cron jobs were cleaned', $count));
        }
    }

    /**
     * Get cron expression of cron job.
     *
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
     * Clean up scheduled jobs that do not match their cron expression anymore.
     *
     * This can happen when you change the cron expression and flush the cache.
     *
     * @return $this
     */
    private function cleanupScheduleMismatches()
    {
        foreach ($this->invalid as $jobCode => $scheduledAtList) {
            $this->cleanup(
                [
                    'status = ?' => Schedule::STATUS_PENDING,
                    'job_code = ?' => $jobCode,
                    'scheduled_at in (?)' => $scheduledAtList,
                ]
            );
        }

        return $this;
    }

    /**
     * Get CronGroup Configuration Value.
     *
     * @param string $groupId
     * @param string $path
     * @return int
     */
    private function getCronGroupConfigurationValue($groupId, $path)
    {
        return $this->_scopeConfig->getValue(
            'system/cron/' . $groupId . '/' . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is Group In Filter.
     *
     * @param string $groupId
     * @return bool
     */
    private function isGroupInFilter($groupId): bool
    {
        return !($this->_request->getParam('group') !== null
            && trim($this->_request->getParam('group'), "'") !== $groupId);
    }

    /**
     * Process pending jobs.
     *
     * @param string $groupId
     * @param array $jobsRoot
     * @param int $currentTime
     */
    private function processPendingJobs($groupId, $jobsRoot, $currentTime)
    {
        $procesedJobs = [];
        $pendingJobs = $this->getPendingSchedules($groupId);
        /** @var Schedule $schedule */
        foreach ($pendingJobs as $schedule) {
            if (isset($procesedJobs[$schedule->getJobCode()])) {
                // process only on job per run
                continue;
            }
            $jobConfig = isset($jobsRoot[$schedule->getJobCode()]) ? $jobsRoot[$schedule->getJobCode()] : null;
            if (!$jobConfig) {
                continue;
            }

            $scheduledTime = strtotime($schedule->getScheduledAt());
            if ($scheduledTime > $currentTime) {
                continue;
            }

            $this->tryRunJob($scheduledTime, $currentTime, $jobConfig, $schedule, $groupId);

            if ($schedule->getStatus() === Schedule::STATUS_SUCCESS) {
                $procesedJobs[$schedule->getJobCode()] = true;
            }

            $this->retrier->execute(
                function () use ($schedule) {
                    $schedule->save();
                },
                $schedule->getResource()->getConnection()
            );
        }
    }

    /**
     * Try to acquire lock for cron job and try to run this job.
     *
     * @param int $scheduledTime
     * @param int $currentTime
     * @param string[] $jobConfig
     * @param Schedule $schedule
     * @param string $groupId
     */
    private function tryRunJob($scheduledTime, $currentTime, $jobConfig, $schedule, $groupId)
    {
        // use sha1 to limit length
        // phpcs:ignore Magento2.Security.InsecureFunction
        $lockName =  self::LOCK_PREFIX . md5($groupId . '_' . $schedule->getJobCode());

        try {
            for ($retries = self::MAX_RETRIES; $retries > 0; $retries--) {
                if ($this->lockManager->lock($lockName, 0) && $schedule->tryLockJob()) {
                    $this->_runJob($scheduledTime, $currentTime, $jobConfig, $schedule, $groupId);
                    break;
                }
                $this->logger->warning("Could not acquire lock for cron job: {$schedule->getJobCode()}");
            }
        } catch (\Exception $e) {
            $this->processError($schedule, $e);
        } finally {
            $this->lockManager->unlock($lockName);
        }
    }

    /**
     * Process error messages.
     *
     * @param Schedule $schedule
     * @param \Exception $exception
     * @return void
     */
    private function processError(Schedule $schedule, \Exception $exception)
    {
        $schedule->setMessages($exception->getMessage());
        if ($schedule->getStatus() === Schedule::STATUS_ERROR) {
            $this->logger->critical($exception);
        }
        if ($schedule->getStatus() === Schedule::STATUS_MISSED
            && $this->state->getMode() === State::MODE_DEVELOPER
        ) {
            $this->logger->info($schedule->getMessages());
        }
    }

    /**
     * Clean up schedule
     *
     * @param mixed $where
     * @return int
     */
    private function cleanup($where = ''): int
    {
        /** @var \Magento\Cron\Model\ResourceModel\Schedule $scheduleResource */
        $scheduleResource = $this->_scheduleFactory->create()->getResource();

        return (int) $this->retrier->execute(
            function () use ($scheduleResource, $where) {
                return $scheduleResource->getConnection()->delete(
                    $scheduleResource->getTable('cron_schedule'),
                    $where
                );
            },
            $scheduleResource->getConnection()
        );
    }
}
