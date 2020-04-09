<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Observer;

use Magento\Cron\Model\Config;
use Magento\Cron\Model\ResourceModel\Schedule as ScheduleResourceModel;
use Magento\Cron\Model\ResourceModel\Schedule\Collection as ScheduleCollection;
use Magento\Cron\Model\Schedule;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Cron\Observer\ProcessCronQueueObserver as ProcessCronQueueObserver;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Console\Request as ConsoleRequest;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\App\State as AppState;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\Process\PhpExecutableFinderFactory;
use Magento\Framework\Profiler\Driver\Standard\Stat;
use Magento\Framework\Profiler\Driver\Standard\StatFactory;
use Magento\Framework\ShellInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ProcessCronQueueObserverTest extends TestCase
{
    /**
     * @var ProcessCronQueueObserver
     */
    private $cronQueueObserver;

    /**
     * @var ObjectManager|MockObject
     */
    private $objectManagerMock;

    /**
     * @var CacheInterface|MockObject
     */
    private $cacheMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var ScheduleFactory|MockObject
     */
    private $scheduleFactoryMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var ConsoleRequest|MockObject
     */
    private $consoleRequestMock;

    /**
     * @var ShellInterface|MockObject
     */
    private $shellMock;

    /**
     * @var ScheduleCollection|MockObject
     */
    private $scheduleCollectionMock;

    /**
     * @var DateTime|MockObject
     */
    private $dateTimeMock;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var AppState|MockObject
     */
    private $appStateMock;

    /**
     * @var LockManagerInterface|MockObject
     */
    private $lockManagerMock;

    /**
     * @var ScheduleResourceModel|MockObject
     */
    private $scheduleResourceMock;

    /**
     * @var MockObject|StatFactory
     */
    private $statFactory;

    /**
     * @var MockObject|Stat
     */
    private $stat;

    /**
     * @var int
     */
    protected $time = 1501538400;

    /**
     * Prepare parameters
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(
            ScopeConfigInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->scheduleCollectionMock = $this->getMockBuilder(
            ScheduleCollection::class
        )->setMethods(
            ['addFieldToFilter', 'load', '__wakeup']
        )->disableOriginalConstructor()->getMock();
        $this->scheduleCollectionMock->expects($this->any())->method('addFieldToFilter')->will($this->returnSelf());
        $this->scheduleCollectionMock->expects($this->any())->method('load')->will($this->returnSelf());

        $this->scheduleFactoryMock = $this->getMockBuilder(
            ScheduleFactory::class
        )->setMethods(
            ['create']
        )->disableOriginalConstructor()->getMock();
        $this->consoleRequestMock = $this->getMockBuilder(
            ConsoleRequest::class
        )->disableOriginalConstructor()->getMock();
        $this->shellMock = $this->getMockBuilder(
            ShellInterface::class
        )->disableOriginalConstructor()->setMethods(
            ['execute']
        )->getMock();
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->appStateMock = $this->getMockBuilder(AppState::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->lockManagerMock = $this->getMockBuilder(LockManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->lockManagerMock->method('lock')->willReturn(true);
        $this->lockManagerMock->method('unlock')->willReturn(true);

        $this->observerMock = $this->createMock(Observer::class);

        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock->expects($this->any())->method('gmtTimestamp')->will($this->returnValue($this->time));

        $phpExecutableFinder = $this->createMock(PhpExecutableFinder::class);
        $phpExecutableFinder->expects($this->any())->method('find')->willReturn('php');
        $phpExecutableFinderFactory = $this->createMock(PhpExecutableFinderFactory::class);
        $phpExecutableFinderFactory->expects($this->any())->method('create')->willReturn($phpExecutableFinder);

        $this->scheduleResourceMock = $this->getMockBuilder(ScheduleResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scheduleResourceMock->method('getConnection')->willReturn($connection);
        $connection->method('delete')->willReturn(1);

        $this->statFactory = $this->getMockBuilder(StatFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->stat = $this->getMockBuilder(Stat::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->statFactory->expects($this->any())->method('create')->willReturn($this->stat);

        $this->cronQueueObserver = new ProcessCronQueueObserver(
            $this->objectManagerMock,
            $this->scheduleFactoryMock,
            $this->cacheMock,
            $this->configMock,
            $this->scopeConfigMock,
            $this->consoleRequestMock,
            $this->shellMock,
            $this->dateTimeMock,
            $phpExecutableFinderFactory,
            $this->loggerMock,
            $this->appStateMock,
            $this->statFactory,
            $this->lockManagerMock
        );
    }

    /**
     * Test case for not existed cron jobs in files but in data base is presented
     */
    public function testDispatchNoJobConfig()
    {
        $lastRun = $this->time + 10000000;
        $this->cacheMock->expects($this->atLeastOnce())->method('load')->will($this->returnValue($lastRun));
        $this->scopeConfigMock->expects($this->atLeastOnce())->method('getValue')->will($this->returnValue(0));

        $this->configMock->expects(
            $this->atLeastOnce()
        )->method(
            'getJobs'
        )->will(
            $this->returnValue(['test_job1' => ['test_data']])
        );

        $schedule = $this->createPartialMock(Schedule::class, ['getJobCode', '__wakeup']);
        $schedule->expects($this->atLeastOnce())
            ->method('getJobCode')
            ->will($this->returnValue('not_existed_job_code'));

        $this->scheduleCollectionMock->addItem($schedule);

        $scheduleMock = $this->getMockBuilder(
            Schedule::class
        )->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->atLeastOnce())
            ->method('getCollection')
            ->will($this->returnValue($this->scheduleCollectionMock));
        $this->scheduleFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($scheduleMock));

        $this->cronQueueObserver->execute($this->observerMock);
    }

    /**
     * Test case checks if some job can't be locked
     */
    public function testDispatchCanNotLock()
    {
        $lastRun = $this->time + 10000000;
        $this->cacheMock->expects($this->any())->method('load')->will($this->returnValue($lastRun));
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')->will($this->returnValue(0));
        $this->consoleRequestMock->expects($this->any())
            ->method('getParam')->will($this->returnValue('test_group'));

        $dateScheduledAt = date('Y-m-d H:i:s', $this->time - 86400);
        $schedule = $this->getMockBuilder(
            Schedule::class
        )->setMethods(
            ['getJobCode', 'tryLockJob', 'getScheduledAt', '__wakeup', 'save', 'setFinishedAt']
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('test_job1'));
        $schedule->expects($this->atLeastOnce())->method('getScheduledAt')->will($this->returnValue($dateScheduledAt));
        $schedule->expects($this->once())->method('tryLockJob')->will($this->returnValue(false));
        $schedule->expects($this->never())->method('setFinishedAt');

        $abstractModel = $this->createMock(\Magento\Framework\Model\AbstractModel::class);
        $schedule->expects($this->any())->method('save')->will($this->returnValue($abstractModel));
        $this->scheduleCollectionMock->addItem($schedule);

        $this->configMock->expects($this->exactly(2))
            ->method('getJobs')
            ->will($this->returnValue(['test_group' => ['test_job1' => ['test_data']]]));

        $scheduleMock = $this->getMockBuilder(Schedule::class)->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())
            ->method('getCollection')->will($this->returnValue($this->scheduleCollectionMock));
        $scheduleMock->expects($this->any())
            ->method('getResource')->will($this->returnValue($this->scheduleResourceMock));
        $this->scheduleFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($scheduleMock));

        $this->cronQueueObserver->execute($this->observerMock);
    }

    /**
     * Test case catch exception if too late for schedule
     */
    public function testDispatchExceptionTooLate()
    {
        $exceptionMessage = 'Cron Job test_job1 is missed at 2017-07-30 15:00:00';
        $jobCode = 'test_job1';

        $lastRun = $this->time + 10000000;
        $this->cacheMock->expects($this->any())->method('load')->willReturn($lastRun);
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturn(0);
        $this->consoleRequestMock->expects($this->any())->method('getParam')->willReturn('test_group');

        $dateScheduledAt = date('Y-m-d H:i:s', $this->time - 86400);
        $schedule = $this->getMockBuilder(
            Schedule::class
        )->setMethods(
            [
                'getJobCode',
                'tryLockJob',
                'getScheduledAt',
                'save',
                'setStatus',
                'setMessages',
                '__wakeup',
                'getStatus',
                'getMessages',
                'getScheduleId',
            ]
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->atLeastOnce())->method('getJobCode')->willReturn($jobCode);
        $schedule->expects($this->atLeastOnce())->method('getScheduledAt')->willReturn($dateScheduledAt);
        $schedule->expects($this->once())->method('tryLockJob')->willReturn(true);
        $schedule->expects(
            $this->any()
        )->method(
            'setStatus'
        )->with(
            $this->equalTo(Schedule::STATUS_MISSED)
        )->willReturnSelf();
        $schedule->expects($this->once())->method('setMessages')->with($this->equalTo($exceptionMessage));
        $schedule->expects($this->atLeastOnce())->method('getStatus')->willReturn(Schedule::STATUS_MISSED);
        $schedule->expects($this->atLeastOnce())->method('getMessages')->willReturn($exceptionMessage);
        $schedule->expects($this->once())->method('save');

        $this->appStateMock->expects($this->once())->method('getMode')->willReturn(State::MODE_DEVELOPER);

        $this->loggerMock->expects($this->once())->method('info')
            ->with('Cron Job test_job1 is missed at 2017-07-30 15:00:00');

        $this->scheduleCollectionMock->addItem($schedule);

        $this->configMock->expects(
            $this->exactly(2)
        )->method(
            'getJobs'
        )->willReturn(
            ['test_group' => ['test_job1' => ['test_data']]]
        );

        $scheduleMock = $this->getMockBuilder(Schedule::class)
            ->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())
            ->method('getCollection')->willReturn($this->scheduleCollectionMock);
        $scheduleMock->expects($this->any())
            ->method('getResource')->will($this->returnValue($this->scheduleResourceMock));
        $this->scheduleFactoryMock->expects($this->atLeastOnce())
            ->method('create')->willReturn($scheduleMock);

        $this->cronQueueObserver->execute($this->observerMock);
    }

    /**
     * Test case catch exception if callback not exist
     */
    public function testDispatchExceptionNoCallback()
    {
        $jobName = 'test_job1';
        $exceptionMessage = 'No callbacks found for cron job ' . $jobName;
        $exception = new \Exception(__($exceptionMessage));

        $dateScheduledAt = date('Y-m-d H:i:s', $this->time - 86400);
        $schedule = $this->getMockBuilder(
            Schedule::class
        )->setMethods(
            ['getJobCode', 'tryLockJob', 'getScheduledAt', 'save', 'setStatus', 'setMessages', '__wakeup', 'getStatus']
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('test_job1'));
        $schedule->expects($this->once())->method('getScheduledAt')->will($this->returnValue($dateScheduledAt));
        $schedule->expects($this->once())->method('tryLockJob')->will($this->returnValue(true));
        $schedule->expects(
            $this->once()
        )->method(
            'setStatus'
        )->with(
            $this->equalTo(Schedule::STATUS_ERROR)
        )->will(
            $this->returnSelf()
        );
        $schedule->expects($this->once())->method('setMessages')->with($this->equalTo($exceptionMessage));
        $schedule->expects($this->any())->method('getStatus')->willReturn(Schedule::STATUS_ERROR);
        $schedule->expects($this->once())->method('save');
        $this->consoleRequestMock->expects($this->any())
            ->method('getParam')->will($this->returnValue('test_group'));
        $this->scheduleCollectionMock->addItem($schedule);

        $this->loggerMock->expects($this->once())->method('critical')->with($exception);

        $jobConfig = ['test_group' => [$jobName => ['instance' => 'Some_Class']]];

        $this->configMock->expects($this->exactly(2))
            ->method('getJobs')->will($this->returnValue($jobConfig));

        $lastRun = $this->time + 10000000;
        $this->cacheMock->expects($this->any())->method('load')->will($this->returnValue($lastRun));

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($this->time + 86400));

        $scheduleMock = $this->getMockBuilder(Schedule::class)
            ->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())
            ->method('getCollection')->will($this->returnValue($this->scheduleCollectionMock));
        $scheduleMock->expects($this->any())
            ->method('getResource')->will($this->returnValue($this->scheduleResourceMock));
        $this->scheduleFactoryMock->expects($this->once())
            ->method('create')->will($this->returnValue($scheduleMock));

        $this->cronQueueObserver->execute($this->observerMock);
    }

    /**
     * Test case catch exception if callback is not callable or throws exception
     *
     * @param string $cronJobType
     * @param mixed $cronJobObject
     * @param string $exceptionMessage
     * @param int $saveCalls
     * @param \Exception $exception
     *
     * @dataProvider dispatchExceptionInCallbackDataProvider
     */
    public function testDispatchExceptionInCallback(
        $cronJobType,
        $cronJobObject,
        $exceptionMessage,
        $saveCalls,
        $exception
    ) {
        $jobConfig = [
            'test_group' => [
                'test_job1' => ['instance' => $cronJobType, 'method' => 'execute'],
            ],
        ];

        $this->consoleRequestMock->expects($this->any())
            ->method('getParam')->will($this->returnValue('test_group'));

        $dateScheduledAt = date('Y-m-d H:i:s', $this->time - 86400);
        $schedule = $this->getMockBuilder(
            Schedule::class
        )->setMethods(
            ['getJobCode', 'tryLockJob', 'getScheduledAt', 'save', 'setStatus', 'setMessages', '__wakeup', 'getStatus']
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('test_job1'));
        $schedule->expects($this->once())->method('getScheduledAt')->will($this->returnValue($dateScheduledAt));
        $schedule->expects($this->once())->method('tryLockJob')->will($this->returnValue(true));
        $schedule->expects($this->once())
            ->method('setStatus')
            ->with($this->equalTo(Schedule::STATUS_ERROR))
            ->will($this->returnSelf());
        $schedule->expects($this->once())->method('setMessages')->with($this->equalTo($exceptionMessage));
        $schedule->expects($this->any())->method('getStatus')->willReturn(Schedule::STATUS_ERROR);
        $schedule->expects($this->exactly($saveCalls))->method('save');

        $this->loggerMock->expects($this->once())->method('critical')->with($exception);

        $this->scheduleCollectionMock->addItem($schedule);

        $this->configMock->expects($this->exactly(2))->method('getJobs')->will($this->returnValue($jobConfig));

        $lastRun = $this->time + 10000000;
        $this->cacheMock->expects($this->any())->method('load')->will($this->returnValue($lastRun));
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($this->time + 86400));

        $scheduleMock = $this->getMockBuilder(Schedule::class)
            ->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())
            ->method('getCollection')->will($this->returnValue($this->scheduleCollectionMock));
        $scheduleMock->expects($this->any())
            ->method('getResource')->will($this->returnValue($this->scheduleResourceMock));
        $this->scheduleFactoryMock->expects($this->once())
            ->method('create')->will($this->returnValue($scheduleMock));
        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with($this->equalTo($cronJobType))
            ->will($this->returnValue($cronJobObject));

        $this->cronQueueObserver->execute($this->observerMock);
    }

    /**
     * @return array
     */
    public function dispatchExceptionInCallbackDataProvider()
    {
        $throwable = new \TypeError();
        return [
            'non-callable callback' => [
                'Not_Existed_Class',
                '',
                'Invalid callback: Not_Existed_Class::execute can\'t be called',
                1,
                new \Exception(__('Invalid callback: Not_Existed_Class::execute can\'t be called'))
            ],
            'exception in execution' => [
                'CronJobException',
                new \Magento\Cron\Test\Unit\Model\CronJobException(),
                'Test exception',
                2,
                new \Exception(__('Test exception'))
            ],
            'throwable in execution' => [
                'CronJobException',
                new \Magento\Cron\Test\Unit\Model\CronJobException(
                    $throwable
                ),
                'Error when running a cron job',
                2,
                new \RuntimeException(
                    'Error when running a cron job',
                    0,
                    $throwable
                )
            ],
        ];
    }

    /**
     * Test case, successfully run job
     */
    public function testDispatchRunJob()
    {
        $jobConfig = [
            'test_group' => ['test_job1' => ['instance' => 'CronJob', 'method' => 'execute']],
        ];
        $this->consoleRequestMock->expects($this->any())->method('getParam')->will($this->returnValue('test_group'));

        $dateScheduledAt = date('Y-m-d H:i:s', $this->time - 86400);
        $scheduleMethods = [
            'getJobCode',
            'tryLockJob',
            'getScheduledAt',
            'save',
            'setStatus',
            'setMessages',
            'setExecutedAt',
            'setFinishedAt',
            '__wakeup',
        ];
        /** @var Schedule|MockObject $schedule */
        $schedule = $this->getMockBuilder(
            Schedule::class
        )->setMethods(
            $scheduleMethods
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('test_job1'));
        $schedule->expects($this->atLeastOnce())->method('getScheduledAt')->will($this->returnValue($dateScheduledAt));
        $schedule->expects($this->atLeastOnce())->method('tryLockJob')->will($this->returnValue(true));
        $schedule->expects($this->any())->method('setFinishedAt')->willReturnSelf();

        // cron start to execute some job
        $schedule->expects($this->any())->method('setExecutedAt')->will($this->returnSelf());
        $schedule->expects($this->atLeastOnce())->method('save');

        // cron end execute some job
        $schedule->expects(
            $this->atLeastOnce()
        )->method(
            'setStatus'
        )->with(
            $this->equalTo(Schedule::STATUS_SUCCESS)
        )->willReturnSelf();

        $schedule->expects($this->at(8))->method('save');

        $this->scheduleCollectionMock->addItem($schedule);

        $this->configMock->expects($this->exactly(2))->method('getJobs')->will($this->returnValue($jobConfig));

        $lastRun = $this->time + 10000000;
        $this->cacheMock->expects($this->any())->method('load')->will($this->returnValue($lastRun));
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($this->time + 86400));

        $scheduleMock = $this->getMockBuilder(
            Schedule::class
        )->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())
            ->method('getCollection')->will($this->returnValue($this->scheduleCollectionMock));
        $scheduleMock->expects($this->any())
            ->method('getResource')->will($this->returnValue($this->scheduleResourceMock));
        $this->scheduleFactoryMock->expects($this->once(2))
            ->method('create')->will($this->returnValue($scheduleMock));

        $testCronJob = $this->getMockBuilder('CronJob')->setMethods(['execute'])->getMock();
        $testCronJob->expects($this->atLeastOnce())->method('execute')->with($schedule);

        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('CronJob')
        )->will(
            $this->returnValue($testCronJob)
        );

        $this->cronQueueObserver->execute($this->observerMock);
    }

    /**
     * Testing _generate(), iterate over saved cron jobs
     */
    public function testDispatchNotGenerate()
    {
        $jobConfig = [
            'test_group' => ['test_job1' => ['instance' => 'CronJob', 'method' => 'execute']],
        ];

        $this->configMock->expects($this->at(0))->method('getJobs')->will($this->returnValue($jobConfig));
        $this->configMock->expects(
            $this->at(1)
        )->method(
            'getJobs'
        )->will(
            $this->returnValue(['test_group' => []])
        );
        $this->configMock->expects($this->at(2))
            ->method('getJobs')->will($this->returnValue($jobConfig));
        $this->configMock->expects($this->at(3))
            ->method('getJobs')->will($this->returnValue($jobConfig));
        $this->consoleRequestMock->expects($this->any())
            ->method('getParam')->will($this->returnValue('test_group'));
        $this->cacheMock->expects(
            $this->at(0)
        )->method(
            'load'
        )->with(
            $this->equalTo(ProcessCronQueueObserver::CACHE_KEY_LAST_HISTORY_CLEANUP_AT . 'test_group')
        )->will(
            $this->returnValue($this->time + 10000000)
        );
        $this->cacheMock->expects(
            $this->at(1)
        )->method(
            'load'
        )->with(
            $this->equalTo(ProcessCronQueueObserver::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT . 'test_group')
        )->will(
            $this->returnValue($this->time - 10000000)
        );

        $this->scopeConfigMock->expects($this->any())->method('getValue')->will($this->returnValue(0));

        $schedule = $this->getMockBuilder(
            Schedule::class
        )->setMethods(
            ['getJobCode', 'getScheduledAt', '__wakeup']
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('job_code1'));
        $schedule->expects($this->once())->method('getScheduledAt')->will($this->returnValue('* * * * *'));

        $this->scheduleCollectionMock->addItem(new \Magento\Framework\DataObject());
        $this->scheduleCollectionMock->addItem($schedule);

        $this->cacheMock->expects($this->any())->method('save');

        $scheduleMock = $this->getMockBuilder(
            Schedule::class
        )->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())
            ->method('getCollection')->will($this->returnValue($this->scheduleCollectionMock));
        $this->scheduleFactoryMock->expects($this->any())->method('create')->will($this->returnValue($scheduleMock));

        $this->scheduleFactoryMock->expects($this->any())->method('create')->will($this->returnValue($schedule));

        $this->cronQueueObserver->execute($this->observerMock);
    }

    /**
     * Testing _generate(), iterate over saved cron jobs and generate jobs
     */
    public function testDispatchGenerate()
    {
        $jobConfig = [
            'default' => [
                'test_job1' => [
                    'instance' => 'CronJob',
                    'method' => 'execute',
                ],
            ],
        ];

        $jobs = [
            'default' => [
                'job1' => ['config_path' => 'test/path'],
                'job2' => ['schedule' => ''],
                'job3' => ['schedule' => '* * * * *'],
            ],
        ];
        $this->configMock->expects($this->at(0))->method('getJobs')->willReturn($jobConfig);
        $this->configMock->expects($this->at(1))->method('getJobs')->willReturn($jobs);
        $this->configMock->expects($this->at(2))->method('getJobs')->willReturn($jobs);
        $this->configMock->expects($this->at(3))->method('getJobs')->willReturn($jobs);
        $this->consoleRequestMock->expects($this->any())->method('getParam')->willReturn('default');
        $this->cacheMock->expects(
            $this->at(0)
        )->method(
            'load'
        )->with(
            $this->equalTo(ProcessCronQueueObserver::CACHE_KEY_LAST_HISTORY_CLEANUP_AT . 'default')
        )->willReturn($this->time + 10000000);
        $this->cacheMock->expects(
            $this->at(1)
        )->method(
            'load'
        )->with(
            $this->equalTo(ProcessCronQueueObserver::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT . 'default')
        )->willReturn($this->time - 10000000);

        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturnMap(
            [
                [
                    'system/cron/default/schedule_generate_every',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    null,
                    0
                ],
                [
                    'system/cron/default/schedule_ahead_for',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    null,
                    2
                ]
            ]
        );

        $schedule = $this->getMockBuilder(
            Schedule::class
        )->setMethods(
            ['getJobCode', 'save', 'getScheduledAt', 'unsScheduleId', 'trySchedule', 'getCollection', 'getResource']
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->willReturn('job_code1');
        $schedule->expects($this->once())->method('getScheduledAt')->willReturn('* * * * *');
        $schedule->expects($this->any())->method('unsScheduleId')->willReturnSelf();
        $schedule->expects($this->any())->method('trySchedule')->willReturnSelf();
        $schedule->expects($this->any())->method('getCollection')->willReturn($this->scheduleCollectionMock);
        $schedule->expects($this->atLeastOnce())->method('save')->willReturnSelf();
        $schedule->expects($this->any())->method('getResource')->will($this->returnValue($this->scheduleResourceMock));

        $this->scheduleCollectionMock->addItem(new \Magento\Framework\DataObject());
        $this->scheduleCollectionMock->addItem($schedule);

        $this->cacheMock->expects($this->any())->method('save');

        $this->scheduleFactoryMock->expects($this->any())->method('create')->willReturn($schedule);

        $this->cronQueueObserver->execute($this->observerMock);
    }

    /**
     * Test case without saved cron jobs in data base
     */
    public function testDispatchCleanup()
    {
        $jobConfig = [
            'test_group' => ['test_job1' => ['instance' => 'CronJob', 'method' => 'execute']],
        ];

        $dateExecutedAt = date('Y-m-d H:i:s', $this->time - 86400);
        $schedule = $this->getMockBuilder(Schedule::class)
            ->disableOriginalConstructor()->setMethods(['getExecutedAt', 'getStatus', 'delete', '__wakeup'])->getMock();
        $schedule->expects($this->any())->method('getExecutedAt')->will($this->returnValue($dateExecutedAt));
        $schedule->expects($this->any())->method('getStatus')->will($this->returnValue('success'));
        $this->consoleRequestMock->expects($this->any())
            ->method('getParam')->will($this->returnValue('test_group'));
        $this->scheduleCollectionMock->addItem($schedule);

        $this->configMock->expects($this->atLeastOnce())->method('getJobs')->will($this->returnValue($jobConfig));

        $this->cacheMock->expects($this->at(0))
            ->method('load')->will($this->returnValue($this->time + 10000000));
        $this->cacheMock->expects($this->at(1))
            ->method('load')->will($this->returnValue($this->time - 10000000));

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')->will($this->returnValue(0));

        $scheduleMock = $this->getMockBuilder(
            Schedule::class
        )->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())
            ->method('getCollection')->will($this->returnValue($this->scheduleCollectionMock));
        $this->scheduleFactoryMock->expects($this->at(0))
            ->method('create')->will($this->returnValue($scheduleMock));

        $collection = $this->getMockBuilder(ScheduleCollection::class)
            ->setMethods(['addFieldToFilter', 'load', '__wakeup'])
            ->disableOriginalConstructor()->getMock();
        $collection->expects($this->any())
            ->method('addFieldToFilter')->will($this->returnSelf());
        $collection->expects($this->any())
            ->method('load')->will($this->returnSelf());
        $collection->addItem($schedule);

        $scheduleMock = $this->getMockBuilder(
            Schedule::class
        )->setMethods(['getCollection', 'getResource'])->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())
            ->method('getCollection')->will($this->returnValue($collection));
        $scheduleMock->expects($this->any())
            ->method('getResource')->will($this->returnValue($this->scheduleResourceMock));
        $this->scheduleFactoryMock->expects($this->any())
            ->method('create')->will($this->returnValue($scheduleMock));

        $this->cronQueueObserver->execute($this->observerMock);
    }

    public function testMissedJobsCleanedInTime()
    {
        /* 1. Initialize dependencies of _cleanup() method which is called first */
        $scheduleMock = $this->getMockBuilder(
            Schedule::class
        )->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())
            ->method('getCollection')->will($this->returnValue($this->scheduleCollectionMock));
        //get configuration value CACHE_KEY_LAST_HISTORY_CLEANUP_AT in the "_cleanup()"
        $this->cacheMock->expects($this->at(0))
            ->method('load')->will($this->returnValue($this->time - 10000000));
        $this->scheduleFactoryMock->expects($this->at(0))
            ->method('create')->will($this->returnValue($scheduleMock));

        /* 2. Initialize dependencies of _generate() method which is called second */
        $jobConfig = [
            'test_group' => ['test_job1' => ['instance' => 'CronJob', 'method' => 'execute']],
        ];
        //get configuration value CACHE_KEY_LAST_HISTORY_CLEANUP_AT in the "_generate()"
        $this->cacheMock->expects($this->at(2))
            ->method('load')->will($this->returnValue($this->time + 10000000));
        $this->scheduleFactoryMock->expects($this->at(2))
            ->method('create')->will($this->returnValue($scheduleMock));

        $this->configMock->expects($this->atLeastOnce())
            ->method('getJobs')->will($this->returnValue($jobConfig));

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap([
                ['system/cron/test_group/use_separate_process', 0],
                ['system/cron/test_group/history_cleanup_every', 10],
                ['system/cron/test_group/schedule_lifetime', 2 * 24 * 60],
                ['system/cron/test_group/history_success_lifetime', 0],
                ['system/cron/test_group/history_failure_lifetime', 0],
                ['system/cron/test_group/schedule_generate_every', 0],
            ]);

        $this->scheduleCollectionMock->expects($this->any())->method('addFieldToFilter')->will($this->returnSelf());
        $this->scheduleCollectionMock->expects($this->any())->method('load')->will($this->returnSelf());

        $scheduleMock->expects($this->any())
            ->method('getCollection')->will($this->returnValue($this->scheduleCollectionMock));
        $scheduleMock->expects($this->any())
            ->method('getResource')->will($this->returnValue($this->scheduleResourceMock));
        $this->scheduleFactoryMock->expects($this->at(1))
            ->method('create')->will($this->returnValue($scheduleMock));

        $this->cronQueueObserver->execute($this->observerMock);
    }
}
