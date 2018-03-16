<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Observer;

use Magento\Cron\Model\Schedule;
use Magento\Cron\Observer\ProcessCronQueueObserver as ProcessCronQueueObserver;

/**
 * Class \Magento\Cron\Test\Unit\Model\ObserverTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessCronQueueObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessCronQueueObserver
     */
    protected $_observer;

    /**
     * @var \Magento\Framework\App\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cache;

    /**
     * @var \Magento\Cron\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_config;

    /**
     * @var \Magento\Cron\Model\ScheduleFactory
     */
    protected $_scheduleFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\Console\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var \Magento\Framework\ShellInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shell;

    /** @var \Magento\Cron\Model\ResourceModel\Schedule\Collection|\PHPUnit_Framework_MockObject_MockObject */
    protected $_collection;

    /**
     * @var \Magento\Cron\Model\Groups\Config\Data
     */
    protected $_cronGroupConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $observer;

    /**
     * Prepare parameters
     */
    protected function setUp()
    {
        $this->_objectManager = $this->getMockBuilder(
            'Magento\Framework\App\ObjectManager'
        )->disableOriginalConstructor()->getMock();
        $this->_cache = $this->getMock('Magento\Framework\App\CacheInterface');
        $this->_config = $this->getMockBuilder('Magento\Cron\Model\Config')->disableOriginalConstructor()->getMock();
        $this->_scopeConfig = $this->getMockBuilder(
            'Magento\Framework\App\Config\ScopeConfigInterface'
        )->disableOriginalConstructor()->getMock();
        $this->_collection = $this->getMockBuilder(
            'Magento\Cron\Model\ResourceModel\Schedule\Collection'
        )->setMethods(
            ['addFieldToFilter', 'load', '__wakeup']
        )->disableOriginalConstructor()->getMock();
        $this->_collection->expects($this->any())->method('addFieldToFilter')->will($this->returnSelf());
        $this->_collection->expects($this->any())->method('load')->will($this->returnSelf());
        $this->_scheduleFactory = $this->getMockBuilder(
            'Magento\Cron\Model\ScheduleFactory'
        )->setMethods(
            ['create']
        )->disableOriginalConstructor()->getMock();
        $this->_request = $this->getMockBuilder(
            'Magento\Framework\App\Console\Request'
        )->disableOriginalConstructor()->getMock();
        $this->_shell = $this->getMockBuilder(
            'Magento\Framework\ShellInterface'
        )->disableOriginalConstructor()->setMethods(
            ['execute']
        )->getMock();

        $this->observer = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);

        $this->timezone = $this->getMock('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $this->timezone->expects($this->any())->method('scopeTimeStamp')->will($this->returnValue(time()));

        $phpExecutableFinder = $this->getMock('Symfony\Component\Process\PhpExecutableFinder', [], [], '', false);
        $phpExecutableFinder->expects($this->any())->method('find')->willReturn('php');
        $phpExecutableFinderFactory = $this->getMock(
            'Magento\Framework\Process\PhpExecutableFinderFactory',
            [],
            [],
            '',
            false
        );
        $phpExecutableFinderFactory->expects($this->any())->method('create')->willReturn($phpExecutableFinder);

        $this->_observer = new ProcessCronQueueObserver(
            $this->_objectManager,
            $this->_scheduleFactory,
            $this->_cache,
            $this->_config,
            $this->_scopeConfig,
            $this->_request,
            $this->_shell,
            $this->timezone,
            $phpExecutableFinderFactory
        );
    }

    /**
     * Test case without saved cron jobs in data base
     */
    public function testDispatchNoPendingJobs()
    {
        $lastRun = time() + 10000000;
        $this->_cache->expects($this->any())->method('load')->will($this->returnValue($lastRun));
        $this->_scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue(0));

        $this->_config->expects($this->once())->method('getJobs')->will($this->returnValue([]));

        $scheduleMock = $this->getMockBuilder('Magento\Cron\Model\Schedule')->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $this->_scheduleFactory->expects($this->once())->method('create')->will($this->returnValue($scheduleMock));

        $this->_observer->execute($this->observer);
    }

    /**
     * Test case for not existed cron jobs in files but in data base is presented
     */
    public function testDispatchNoJobConfig()
    {
        $lastRun = time() + 10000000;
        $this->_cache->expects($this->any())->method('load')->will($this->returnValue($lastRun));
        $this->_scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue(0));

        $this->_config->expects(
            $this->once()
        )->method(
            'getJobs'
        )->will(
            $this->returnValue(['test_job1' => ['test_data']])
        );

        $schedule = $this->getMock('Magento\Cron\Model\Schedule', ['getJobCode', '__wakeup'], [], '', false);
        $schedule->expects($this->once())->method('getJobCode')->will($this->returnValue('not_existed_job_code'));

        $this->_collection->addItem($schedule);

        $this->_config->expects($this->once())->method('getJobs')->will($this->returnValue([]));

        $scheduleMock = $this->getMockBuilder('Magento\Cron\Model\Schedule')->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $this->_scheduleFactory->expects($this->once())->method('create')->will($this->returnValue($scheduleMock));

        $this->_observer->execute($this->observer);
    }

    /**
     * Test case checks if some job can't be locked
     */
    public function testDispatchCanNotLock()
    {
        $lastRun = time() + 10000000;
        $this->_cache->expects($this->any())->method('load')->will($this->returnValue($lastRun));
        $this->_scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue(0));
        $this->_request->expects($this->any())->method('getParam')->will($this->returnValue('test_group'));
        $schedule = $this->getMockBuilder(
            'Magento\Cron\Model\Schedule'
        )->setMethods(
            ['getJobCode', 'tryLockJob', 'getScheduledAt', '__wakeup', 'save']
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('test_job1'));
        $schedule->expects($this->once())->method('getScheduledAt')->will($this->returnValue('-1 day'));
        $schedule->expects($this->once())->method('tryLockJob')->will($this->returnValue(false));
        $abstractModel = $this->getMock('Magento\Framework\Model\AbstractModel', [], [], '', false);
        $schedule->expects($this->any())->method('save')->will($this->returnValue($abstractModel));
        $this->_collection->addItem($schedule);

        $this->_config->expects(
            $this->once()
        )->method(
            'getJobs'
        )->will(
            $this->returnValue(['test_group' => ['test_job1' => ['test_data']]])
        );

        $scheduleMock = $this->getMockBuilder('Magento\Cron\Model\Schedule')->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $this->_scheduleFactory->expects($this->once())->method('create')->will($this->returnValue($scheduleMock));

        $this->_observer->execute($this->observer);
    }

    /**
     * Test case catch exception if to late for schedule
     */
    public function testDispatchExceptionTooLate()
    {
        $exceptionMessage = 'Too late for the schedule';

        $lastRun = time() + 10000000;
        $this->_cache->expects($this->any())->method('load')->will($this->returnValue($lastRun));
        $this->_scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue(0));
        $this->_request->expects($this->any())->method('getParam')->will($this->returnValue('test_group'));
        $schedule = $this->getMockBuilder(
            'Magento\Cron\Model\Schedule'
        )->setMethods(
            ['getJobCode', 'tryLockJob', 'getScheduledAt', 'save', 'setStatus', 'setMessages', '__wakeup']
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('test_job1'));
        $schedule->expects($this->once())->method('getScheduledAt')->will($this->returnValue('-1 day'));
        $schedule->expects($this->once())->method('tryLockJob')->will($this->returnValue(true));
        $schedule->expects(
            $this->once()
        )->method(
            'setStatus'
        )->with(
            $this->equalTo(\Magento\Cron\Model\Schedule::STATUS_MISSED)
        )->will(
            $this->returnSelf()
        );
        $schedule->expects($this->once())->method('setMessages')->with($this->equalTo($exceptionMessage));
        $schedule->expects($this->once())->method('save');

        $this->_collection->addItem($schedule);

        $this->_config->expects(
            $this->once()
        )->method(
            'getJobs'
        )->will(
            $this->returnValue(['test_group' => ['test_job1' => ['test_data']]])
        );

        $scheduleMock = $this->getMockBuilder('Magento\Cron\Model\Schedule')->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $this->_scheduleFactory->expects($this->once())->method('create')->will($this->returnValue($scheduleMock));

        $this->_observer->execute($this->observer);
    }

    /**
     * Test case catch exception if callback not exist
     */
    public function testDispatchExceptionNoCallback()
    {
        $exceptionMessage = 'No callbacks found';

        $schedule = $this->getMockBuilder(
            'Magento\Cron\Model\Schedule'
        )->setMethods(
            ['getJobCode', 'tryLockJob', 'getScheduledAt', 'save', 'setStatus', 'setMessages', '__wakeup']
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('test_job1'));
        $schedule->expects($this->once())->method('getScheduledAt')->will($this->returnValue('-1 day'));
        $schedule->expects($this->once())->method('tryLockJob')->will($this->returnValue(true));
        $schedule->expects(
            $this->once()
        )->method(
            'setStatus'
        )->with(
            $this->equalTo(\Magento\Cron\Model\Schedule::STATUS_ERROR)
        )->will(
            $this->returnSelf()
        );
        $schedule->expects($this->once())->method('setMessages')->with($this->equalTo($exceptionMessage));
        $schedule->expects($this->once())->method('save');
        $this->_request->expects($this->any())->method('getParam')->will($this->returnValue('test_group'));
        $this->_collection->addItem($schedule);

        $jobConfig = ['test_group' => ['test_job1' => ['instance' => 'Some_Class']]];

        $this->_config->expects($this->once())->method('getJobs')->will($this->returnValue($jobConfig));

        $lastRun = time() + 10000000;
        $this->_cache->expects($this->any())->method('load')->will($this->returnValue($lastRun));

        $this->_scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue(strtotime('+1 day')));

        $scheduleMock = $this->getMockBuilder('Magento\Cron\Model\Schedule')->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $this->_scheduleFactory->expects($this->once())->method('create')->will($this->returnValue($scheduleMock));

        $this->_observer->execute($this->observer);
    }

    /**
     * Test case catch exception if callback is not callable or throws exception
     *
     * @param string $cronJobType
     * @param mixed $cronJobObject
     * @param string $exceptionMessage
     * @param int $saveCalls
     *
     * @dataProvider dispatchExceptionInCallbackDataProvider
     */
    public function testDispatchExceptionInCallback($cronJobType, $cronJobObject, $exceptionMessage, $saveCalls)
    {
        $jobConfig = [
            'test_group' => [
                'test_job1' => ['instance' => $cronJobType, 'method' => 'execute'],
            ],
        ];

        $this->_request->expects($this->any())->method('getParam')->will($this->returnValue('test_group'));
        $schedule = $this->getMockBuilder('Magento\Cron\Model\Schedule')
            ->setMethods(['getJobCode', 'tryLockJob', 'getScheduledAt', 'save', 'setStatus', 'setMessages', '__wakeup'])
            ->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('test_job1'));
        $schedule->expects($this->once())->method('getScheduledAt')->will($this->returnValue('-1 day'));
        $schedule->expects($this->once())->method('tryLockJob')->will($this->returnValue(true));
        $schedule->expects($this->once())
            ->method('setStatus')
            ->with($this->equalTo(\Magento\Cron\Model\Schedule::STATUS_ERROR))
            ->will($this->returnSelf());
        $schedule->expects($this->once())->method('setMessages')->with($this->equalTo($exceptionMessage));
        $schedule->expects($this->exactly($saveCalls))->method('save');

        $this->_collection->addItem($schedule);

        $this->_config->expects($this->once())->method('getJobs')->will($this->returnValue($jobConfig));

        $lastRun = time() + 10000000;
        $this->_cache->expects($this->any())->method('load')->will($this->returnValue($lastRun));
        $this->_scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue(strtotime('+1 day')));

        $scheduleMock = $this->getMockBuilder('Magento\Cron\Model\Schedule')->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $this->_scheduleFactory->expects($this->once())->method('create')->will($this->returnValue($scheduleMock));
        $this->_objectManager
            ->expects($this->once())
            ->method('create')
            ->with($this->equalTo($cronJobType))
            ->will($this->returnValue($cronJobObject));

        $this->_observer->execute($this->observer);
    }

    /**
     * @return array
     */
    public function dispatchExceptionInCallbackDataProvider()
    {
        return [
            'non-callable callback' => [
                'Not_Existed_Class',
                '',
                'Invalid callback: Not_Existed_Class::execute can\'t be called',
                1
            ],
            'exception in execution' => [
                'CronJobException',
                new \Magento\Cron\Test\Unit\Model\CronJobException(),
                'Test exception',
                2
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
        $this->_request->expects($this->any())->method('getParam')->will($this->returnValue('test_group'));

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
        /** @var \Magento\Cron\Model\Schedule|\PHPUnit_Framework_MockObject_MockObject $schedule */
        $schedule = $this->getMockBuilder(
            'Magento\Cron\Model\Schedule'
        )->setMethods(
            $scheduleMethods
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('test_job1'));
        $schedule->expects($this->once())->method('getScheduledAt')->will($this->returnValue('-1 day'));
        $schedule->expects($this->once())->method('tryLockJob')->will($this->returnValue(true));

        // cron start to execute some job
        $schedule->expects($this->any())->method('setExecutedAt')->will($this->returnSelf());
        $schedule->expects($this->at(5))->method('save');

        // cron end execute some job
        $schedule->expects(
            $this->at(6)
        )->method(
            'setStatus'
        )->with(
            $this->equalTo(\Magento\Cron\Model\Schedule::STATUS_SUCCESS)
        )->will(
            $this->returnSelf()
        );

        $schedule->expects($this->at(8))->method('save');

        $this->_collection->addItem($schedule);

        $this->_config->expects($this->once())->method('getJobs')->will($this->returnValue($jobConfig));

        $lastRun = time() + 10000000;
        $this->_cache->expects($this->any())->method('load')->will($this->returnValue($lastRun));
        $this->_scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue(strtotime('+1 day')));

        $scheduleMock = $this->getMockBuilder('Magento\Cron\Model\Schedule')->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $this->_scheduleFactory->expects($this->once())->method('create')->will($this->returnValue($scheduleMock));

        $testCronJob = $this->getMockBuilder('CronJob')->setMethods(['execute'])->getMock();
        $testCronJob->expects($this->atLeastOnce())->method('execute')->with($schedule);

        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('CronJob')
        )->will(
            $this->returnValue($testCronJob)
        );

        $this->_observer->execute($this->observer);
    }

    /**
     * Testing _generate(), iterate over saved cron jobs
     */
    public function testDispatchNotGenerate()
    {
        $jobConfig = [
            'test_group' => ['test_job1' => ['instance' => 'CronJob', 'method' => 'execute']],
        ];

        $this->_config->expects($this->at(0))->method('getJobs')->will($this->returnValue($jobConfig));
        $this->_config->expects(
            $this->at(1)
        )->method(
            'getJobs'
        )->will(
            $this->returnValue(['test_group' => []])
        );
        $this->_request->expects($this->any())->method('getParam')->will($this->returnValue('test_group'));
        $this->_cache->expects(
            $this->at(0)
        )->method(
            'load'
        )->with(
            $this->equalTo(ProcessCronQueueObserver::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT . 'test_group')
        )->will(
            $this->returnValue(time() - 10000000)
        );
        $this->_cache->expects(
            $this->at(2)
        )->method(
            'load'
        )->with(
            $this->equalTo(ProcessCronQueueObserver::CACHE_KEY_LAST_HISTORY_CLEANUP_AT . 'test_group')
        )->will(
            $this->returnValue(time() + 10000000)
        );

        $this->_scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue(0));

        $schedule = $this->getMockBuilder(
            'Magento\Cron\Model\Schedule'
        )->setMethods(
            ['getJobCode', 'getScheduledAt', '__wakeup']
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('job_code1'));
        $schedule->expects($this->once())->method('getScheduledAt')->will($this->returnValue('* * * * *'));

        $this->_collection->addItem(new \Magento\Framework\DataObject());
        $this->_collection->addItem($schedule);

        $this->_cache->expects($this->any())->method('save');

        $scheduleMock = $this->getMockBuilder('Magento\Cron\Model\Schedule')->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $this->_scheduleFactory->expects($this->any())->method('create')->will($this->returnValue($scheduleMock));

        $this->_scheduleFactory->expects($this->any())->method('create')->will($this->returnValue($schedule));

        $this->_observer->execute($this->observer);
    }

    /**
     * Testing _generate(), iterate over saved cron jobs and generate jobs
     */
    public function testDispatchGenerate()
    {
        $jobConfig = [
            'test_group' => [
                'test_job1' => [
                    'instance' => 'CronJob',
                    'method' => 'execute',
                ],
            ],
        ];

        $this->_config->expects($this->at(0))->method('getJobs')->will($this->returnValue($jobConfig));
        $jobs = [
            'test_group' => [
                'job1' => ['config_path' => 'test/path'],
                'job2' => ['schedule' => ''],
                'job3' => ['schedule' => '* * * * *'],
            ],
        ];
        $this->_config->expects($this->at(1))->method('getJobs')->will($this->returnValue($jobs));
        $this->_request->expects($this->any())->method('getParam')->will($this->returnValue('test_group'));
        $this->_cache->expects(
            $this->at(0)
        )->method(
            'load'
        )->with(
            $this->equalTo(ProcessCronQueueObserver::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT . 'test_group')
        )->will(
            $this->returnValue(time() - 10000000)
        );
        $this->_cache->expects(
            $this->at(2)
        )->method(
            'load'
        )->with(
            $this->equalTo(ProcessCronQueueObserver::CACHE_KEY_LAST_HISTORY_CLEANUP_AT . 'test_group')
        )->will(
            $this->returnValue(time() + 10000000)
        );

        $this->_scopeConfig->expects($this->any())->method('getValue')->willReturnMap(
            [
                [
                    'system/cron/test_group/schedule_generate_every',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    null,
                    0
                ],
                [
                    'system/cron/test_group/schedule_ahead_for',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    null,
                    2
                ]
            ]
        );

        $schedule = $this->getMockBuilder(
            'Magento\Cron\Model\Schedule'
        )->setMethods(
            ['getJobCode', 'getScheduledAt', 'trySchedule', 'unsScheduleId', 'save', '__wakeup', 'getCollection']
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('job_code1'));
        $schedule->expects($this->once())->method('getScheduledAt')->will($this->returnValue('* * * * *'));
        $schedule->expects($this->any())->method('unsScheduleId')->will($this->returnSelf());
        $schedule->expects($this->any())->method('trySchedule')->will($this->returnSelf());
        $schedule->expects($this->any())->method('getCollection')->willReturn($this->_collection);
        $schedule->expects($this->atLeastOnce())->method('save')->willReturnSelf();

        $this->_collection->addItem(new \Magento\Framework\DataObject());
        $this->_collection->addItem($schedule);

        $this->_cache->expects($this->any())->method('save');

        $this->_scheduleFactory->expects($this->any())->method('create')->will($this->returnValue($schedule));

        $this->_observer->execute($this->observer);
    }

    /**
     * Test case without saved cron jobs in data base
     */
    public function testDispatchCleanup()
    {
        $jobConfig = [
            'test_group' => ['test_job1' => ['instance' => 'CronJob', 'method' => 'execute']],
        ];

        $schedule = $this->getMockBuilder(
            'Magento\Cron\Model\Schedule'
        )->disableOriginalConstructor()->setMethods(
            ['getExecutedAt', 'getStatus', 'delete', '__wakeup']
        )->getMock();
        $schedule->expects($this->any())->method('getExecutedAt')->will($this->returnValue('-1 day'));
        $schedule->expects($this->any())->method('getStatus')->will($this->returnValue('success'));
        $this->_request->expects($this->any())->method('getParam')->will($this->returnValue('test_group'));
        $this->_collection->addItem($schedule);

        $this->_config->expects($this->once())->method('getJobs')->will($this->returnValue($jobConfig));

        $this->_cache->expects($this->at(0))->method('load')->will($this->returnValue(time() + 10000000));
        $this->_cache->expects($this->at(1))->method('load')->will($this->returnValue(time() - 10000000));

        $this->_scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue(0));

        $scheduleMock = $this->getMockBuilder('Magento\Cron\Model\Schedule')->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $this->_scheduleFactory->expects($this->at(0))->method('create')->will($this->returnValue($scheduleMock));

        $collection = $this->getMockBuilder(
            'Magento\Cron\Model\ResourceModel\Schedule\Collection'
        )->setMethods(
            ['addFieldToFilter', 'load', '__wakeup']
        )->disableOriginalConstructor()->getMock();
        $collection->expects($this->any())->method('addFieldToFilter')->will($this->returnSelf());
        $collection->expects($this->any())->method('load')->will($this->returnSelf());
        $collection->addItem($schedule);

        $scheduleMock = $this->getMockBuilder('Magento\Cron\Model\Schedule')->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($collection));
        $this->_scheduleFactory->expects($this->at(1))->method('create')->will($this->returnValue($scheduleMock));

        $this->_observer->execute($this->observer);
    }

    public function testMissedJobsCleanedInTime()
    {
        /* 1. Initialize dependencies of _generate() method which is called first */
        $jobConfig = [
            'test_group' => ['test_job1' => ['instance' => 'CronJob', 'method' => 'execute']],
        ];

        // This item was scheduled 2 days ago
        /** @var \Magento\Cron\Model\Schedule|\PHPUnit_Framework_MockObject_MockObject $schedule1 */
        $schedule1 = $this->getMockBuilder(
            'Magento\Cron\Model\Schedule'
        )->disableOriginalConstructor()->setMethods(
            ['getExecutedAt', 'getScheduledAt', 'getStatus', 'delete', '__wakeup']
        )->getMock();
        $schedule1->expects($this->any())->method('getExecutedAt')->will($this->returnValue(null));
        $schedule1->expects($this->any())->method('getScheduledAt')->will($this->returnValue('-2 day -2 hour'));
        $schedule1->expects($this->any())->method('getStatus')->will($this->returnValue(Schedule::STATUS_MISSED));
        //we expect this job be deleted from the list
        $schedule1->expects($this->once())->method('delete')->will($this->returnValue(true));

        // This item was scheduled 1 day ago
        $schedule2 = $this->getMockBuilder(
            'Magento\Cron\Model\Schedule'
        )->disableOriginalConstructor()->setMethods(
            ['getExecutedAt', 'getScheduledAt', 'getStatus', 'delete', '__wakeup']
        )->getMock();
        $schedule2->expects($this->any())->method('getExecutedAt')->will($this->returnValue(null));
        $schedule2->expects($this->any())->method('getScheduledAt')->will($this->returnValue('-1 day'));
        $schedule2->expects($this->any())->method('getStatus')->will($this->returnValue(Schedule::STATUS_MISSED));
        //we don't expect this job be deleted from the list
        $schedule2->expects($this->never())->method('delete');

        $this->_collection->addItem($schedule1);
        $this->_config->expects($this->once())->method('getJobs')->will($this->returnValue($jobConfig));

        //get configuration value CACHE_KEY_LAST_HISTORY_CLEANUP_AT in the "_generate()"
        $this->_cache->expects($this->at(0))->method('load')->will($this->returnValue(time() + 10000000));
        //get configuration value CACHE_KEY_LAST_HISTORY_CLEANUP_AT in the "_cleanup()"
        $this->_cache->expects($this->at(1))->method('load')->will($this->returnValue(time() - 10000000));

        $this->_scopeConfig->expects($this->at(0))->method('getValue')
            ->with($this->equalTo('system/cron/test_group/use_separate_process'))
            ->will($this->returnValue(0));
        $this->_scopeConfig->expects($this->at(1))->method('getValue')
            ->with($this->equalTo('system/cron/test_group/schedule_generate_every'))
            ->will($this->returnValue(0));
        $this->_scopeConfig->expects($this->at(2))->method('getValue')
            ->with($this->equalTo('system/cron/test_group/history_cleanup_every'))
            ->will($this->returnValue(0));
        $this->_scopeConfig->expects($this->at(3))->method('getValue')
            ->with($this->equalTo('system/cron/test_group/schedule_lifetime'))
            ->will($this->returnValue(2*24*60));
        $this->_scopeConfig->expects($this->at(4))->method('getValue')
            ->with($this->equalTo('system/cron/test_group/history_success_lifetime'))
            ->will($this->returnValue(0));
        $this->_scopeConfig->expects($this->at(5))->method('getValue')
            ->with($this->equalTo('system/cron/test_group/history_failure_lifetime'))
            ->will($this->returnValue(0));

        /* 2. Initialize dependencies of _cleanup() method which is called second */
        $scheduleMock = $this->getMockBuilder('Magento\Cron\Model\Schedule')->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $this->_scheduleFactory->expects($this->at(0))->method('create')->will($this->returnValue($scheduleMock));

        $collection = $this->getMockBuilder(
            'Magento\Cron\Model\ResourceModel\Schedule\Collection'
        )->setMethods(
            ['addFieldToFilter', 'load', '__wakeup']
        )->disableOriginalConstructor()->getMock();
        $collection->expects($this->any())->method('addFieldToFilter')->will($this->returnSelf());
        $collection->expects($this->any())->method('load')->will($this->returnSelf());
        $collection->addItem($schedule1);
        $collection->addItem($schedule2);

        $scheduleMock = $this->getMockBuilder('Magento\Cron\Model\Schedule')->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($collection));
        $this->_scheduleFactory->expects($this->at(1))->method('create')->will($this->returnValue($scheduleMock));

        $this->_observer->execute($this->observer);
    }
}
