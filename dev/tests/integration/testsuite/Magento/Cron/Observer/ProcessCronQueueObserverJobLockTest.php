<?php
/**
 * Copyright 2026 Adobe
 * All rights reserved.
 */
declare(strict_types=1);

namespace Magento\Cron\Observer;

use Magento\Cron\Model\Schedule;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\Exception\CronException;
use Magento\Framework\Lock\Backend\Database;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[DbIsolation(true)]
class ProcessCronQueueObserverJobLockTest extends TestCase
{
    private const GROUP_ID = 'default';
    private const JOB_CODE = 'test_job_lock_release';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Database
     */
    private $lockManager;

    /**
     * @var string
     */
    private $jobLockName;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->lockManager = $this->objectManager->create(Database::class);
        // phpcs:ignore Magento2.Security.InsecureFunction
        $this->jobLockName = ProcessCronQueueObserver::LOCK_PREFIX . md5(self::GROUP_ID . '_' . self::JOB_CODE);
    }

    protected function tearDown(): void
    {
        $attempts = ProcessCronQueueObserver::MAX_RETRIES;
        while ($attempts-- > 0 && $this->lockManager->isLocked($this->jobLockName)) {
            $this->lockManager->unlock($this->jobLockName);
        }
    }

    public function testJobLockIsReleasedWhenScheduleWasTakenByAnotherProcess(): void
    {
        /** @var Schedule $schedule */
        $schedule = $this->objectManager->get(ScheduleFactory::class)->create();
        $schedule->setJobCode(self::JOB_CODE)
            ->setStatus(Schedule::STATUS_SUCCESS)
            ->setCreatedAt(date('Y-m-d H:i:s'))
            ->setScheduledAt(date('Y-m-d H:i:00'))
            ->save();

        $observer = $this->objectManager->create(
            ProcessCronQueueObserver::class,
            ['lockManager' => $this->lockManager]
        );
        $tryRunJob = new \ReflectionMethod($observer, 'tryRunJob');

        $exception = null;
        try {
            $tryRunJob->invoke($observer, time(), time(), [], $schedule, self::GROUP_ID);
        } catch (CronException $e) {
            $exception = $e;
        }

        $this->assertInstanceOf(CronException::class, $exception);

        $this->assertFalse(
            $this->lockManager->isLocked($this->jobLockName),
            'The cron job lock must be free once the observer is done with a schedule it could not take'
        );
    }
}
