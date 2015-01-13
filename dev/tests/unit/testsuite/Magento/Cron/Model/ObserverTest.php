<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model;

/**
 * Class \Magento\Cron\Model\ObserverTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cron\Model\Observer
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

    /** @var \Magento\Cron\Model\Resource\Schedule\Collection|\PHPUnit_Framework_MockObject_MockObject */
    protected $_collection;

    /**
     * @var \Magento\Cron\Model\Groups\Config\Data
     */
    protected $_cronGroupConfig;

    /**
     * Prepare parameters
     */
    public function setUp()
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
            'Magento\Cron\Model\Resource\Schedule\Collection'
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

        $this->_observer = new \Magento\Cron\Model\Observer(
            $this->_objectManager,
            $this->_scheduleFactory,
            $this->_cache,
            $this->_config,
            $this->_scopeConfig,
            $this->_request,
            $this->_shell
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

        $this->_observer->dispatch('');
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

        $this->_observer->dispatch('');
    }

    /**
     * Test case checks if some job can't be locked
     */
    public function testDispatchCanNotLock()
    {
        $lastRun = time() + 10000000;
        $this->_cache->expects($this->any())->method('load')->will($this->returnValue($lastRun));
        $this->_scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue(0));

        $schedule = $this->getMockBuilder(
            'Magento\Cron\Model\Schedule'
        )->setMethods(
            ['getJobCode', 'tryLockJob', 'getScheduledAt', '__wakeup']
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('test_job1'));
        $schedule->expects($this->once())->method('getScheduledAt')->will($this->returnValue('-1 day'));
        $schedule->expects($this->once())->method('tryLockJob')->will($this->returnValue(false));

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

        $this->_observer->dispatch('');
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

        $this->_observer->dispatch('');
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

        $this->_collection->addItem($schedule);

        $jobConfig = ['test_group' => ['test_job1' => ['instance' => 'Some_Class']]];

        $this->_config->expects($this->once())->method('getJobs')->will($this->returnValue($jobConfig));

        $lastRun = time() + 10000000;
        $this->_cache->expects($this->any())->method('load')->will($this->returnValue($lastRun));

        $this->_scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue(strtotime('+1 day')));

        $scheduleMock = $this->getMockBuilder('Magento\Cron\Model\Schedule')->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $this->_scheduleFactory->expects($this->once())->method('create')->will($this->returnValue($scheduleMock));

        $this->_observer->dispatch('');
    }

    /**
     * Test case catch exception if callback exists but can't be executed
     */
    public function testDispatchExceptionNotExecutable()
    {
        $jobConfig = [
            'test_group' => [
                'test_job1' => ['instance' => 'Not_Existed_Class', 'method' => 'notExistedMethod'],
            ],
        ];

        $exceptionMessage = 'Invalid callback: Not_Existed_Class::notExistedMethod can\'t be called';

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

        $this->_collection->addItem($schedule);

        $this->_config->expects($this->once())->method('getJobs')->will($this->returnValue($jobConfig));

        $lastRun = time() + 10000000;
        $this->_cache->expects($this->any())->method('load')->will($this->returnValue($lastRun));
        $this->_scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue(strtotime('+1 day')));

        $scheduleMock = $this->getMockBuilder('Magento\Cron\Model\Schedule')->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $this->_scheduleFactory->expects($this->once())->method('create')->will($this->returnValue($scheduleMock));
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('Not_Existed_Class')
        )->will(
            $this->returnValue('')
        );

        $this->_observer->dispatch('');
    }

    /**
     * Test case, successfully run job
     */
    public function testDispatchRunJob()
    {
        require_once __DIR__ . '/CronJob.php';
        $testCronJob = new \Magento\Cron\Model\CronJob();

        $jobConfig = [
            'test_group' => ['test_job1' => ['instance' => 'CronJob', 'method' => 'execute']],
        ];

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
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('CronJob')
        )->will(
            $this->returnValue($testCronJob)
        );

        $this->_observer->dispatch('');

        $this->assertInstanceOf('Magento\Cron\Model\Schedule', $testCronJob->getParam());
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

        $this->_cache->expects(
            $this->at(0)
        )->method(
            'load'
        )->with(
            $this->equalTo(\Magento\Cron\Model\Observer::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT . 'test_group')
        )->will(
            $this->returnValue(time() - 10000000)
        );
        $this->_cache->expects(
            $this->at(2)
        )->method(
            'load'
        )->with(
            $this->equalTo(\Magento\Cron\Model\Observer::CACHE_KEY_LAST_HISTORY_CLEANUP_AT . 'test_group')
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

        $this->_collection->addItem(new \Magento\Framework\Object());
        $this->_collection->addItem($schedule);

        $this->_cache->expects($this->any())->method('save');

        $scheduleMock = $this->getMockBuilder('Magento\Cron\Model\Schedule')->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $this->_scheduleFactory->expects($this->any())->method('create')->will($this->returnValue($scheduleMock));

        $this->_scheduleFactory->expects($this->any())->method('create')->will($this->returnValue($schedule));

        $this->_observer->dispatch('');
    }

    /**
     * Testing _generate(), iterate over saved cron jobs and generate jobs
     */
    public function testDispatchGenerate()
    {
        $jobConfig = [
            'test_group' => [
                'default' => [
                    'test_job1' => [
                        'instance' => 'CronJob',
                        'method' => 'execute',
                    ],
                ],
            ],
        ];

        $this->_config->expects($this->at(0))->method('getJobs')->will($this->returnValue($jobConfig));
        $jobs = [
            'test_group' => [
                'default' => [
                    'job1' => ['config_path' => 'test/path'],
                    'job2' => ['schedule' => ''],
                    'job3' => ['schedule' => '* * * * *'],
                ],
            ],
        ];
        $this->_config->expects($this->at(1))->method('getJobs')->will($this->returnValue($jobs));

        $this->_cache->expects(
            $this->at(0)
        )->method(
            'load'
        )->with(
            $this->equalTo(\Magento\Cron\Model\Observer::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT . 'test_group')
        )->will(
            $this->returnValue(time() - 10000000)
        );
        $this->_cache->expects(
            $this->at(2)
        )->method(
            'load'
        )->with(
            $this->equalTo(\Magento\Cron\Model\Observer::CACHE_KEY_LAST_HISTORY_CLEANUP_AT . 'test_group')
        )->will(
            $this->returnValue(time() + 10000000)
        );

        $this->_scopeConfig->expects($this->at(0))->method('getValue')->will($this->returnValue(0));

        $scheduleMethods = ['getJobCode', 'getScheduledAt', 'trySchedule', 'unsScheduleId', 'save', '__wakeup'];
        $schedule = $this->getMockBuilder(
            'Magento\Cron\Model\Schedule'
        )->setMethods(
            $scheduleMethods
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('job_code1'));
        $schedule->expects($this->once())->method('getScheduledAt')->will($this->returnValue('* * * * *'));
        $schedule->expects($this->any())->method('unsScheduleId')->will($this->returnSelf());
        $schedule->expects($this->any())->method('trySchedule')->will($this->returnSelf());

        $this->_collection->addItem(new \Magento\Framework\Object());
        $this->_collection->addItem($schedule);

        $this->_cache->expects($this->any())->method('save');

        $scheduleMock = $this->getMockBuilder(
            'Magento\Cron\Model\Schedule'
        )->disableOriginalConstructor()->setMethods(
            ['getCollection', '__wakeup']
        )->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $this->_scheduleFactory->expects($this->any())->method('create')->will($this->returnValue($scheduleMock));

        $this->_scheduleFactory->expects($this->any())->method('create')->will($this->returnValue($schedule));

        $this->_observer->dispatch('');
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

        $this->_collection->addItem($schedule);

        $this->_config->expects($this->once())->method('getJobs')->will($this->returnValue($jobConfig));

        $this->_cache->expects($this->at(0))->method('load')->will($this->returnValue(time() + 10000000));
        $this->_cache->expects($this->at(1))->method('load')->will($this->returnValue(time() - 10000000));

        $this->_scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue(0));

        $scheduleMock = $this->getMockBuilder('Magento\Cron\Model\Schedule')->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $this->_scheduleFactory->expects($this->at(0))->method('create')->will($this->returnValue($scheduleMock));

        $collection = $this->getMockBuilder(
            'Magento\Cron\Model\Resource\Schedule\Collection'
        )->setMethods(
            ['addFieldToFilter', 'load', '__wakeup']
        )->disableOriginalConstructor()->getMock();
        $collection->expects($this->any())->method('addFieldToFilter')->will($this->returnSelf());
        $collection->expects($this->any())->method('load')->will($this->returnSelf());
        $collection->addItem($schedule);

        $scheduleMock = $this->getMockBuilder('Magento\Cron\Model\Schedule')->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($collection));
        $this->_scheduleFactory->expects($this->at(1))->method('create')->will($this->returnValue($scheduleMock));

        $this->_observer->dispatch('');
    }
}
