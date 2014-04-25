<?php
/**
 * Test class for \Magento\Framework\Profiler\Driver\Standard\Stat
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Profiler\Driver\Standard;

class StatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Profiler\Driver\Standard\Stat
     */
    protected $_stat;

    protected function setUp()
    {
        $this->_stat = new \Magento\Framework\Profiler\Driver\Standard\Stat();
    }

    /**
     * Test start and stop methods of \Magento\Framework\Profiler\Driver\Standard\Stat
     *
     * @dataProvider actionsDataProvider
     * @param array $actions
     * @param array $expected
     */
    public function testActions(array $actions, array $expected)
    {
        foreach ($actions as $actionData) {
            list($action, $timerId, $time, $realMemory, $emallocMemory) = array_values($actionData);
            $this->_executeTimerAction($action, $timerId, $time, $realMemory, $emallocMemory);
        }

        if (empty($expected)) {
            $this->fail("\$expected mustn't be empty");
        }

        foreach ($expected as $timerId => $expectedTimer) {
            $actualTimer = $this->_stat->get($timerId);
            $this->assertInternalType('array', $actualTimer, "Timer '{$timerId}' must be an array");
            $this->assertEquals($expectedTimer, $actualTimer, "Timer '{$timerId}' has unexpected value");
        }
    }

    /**
     * Data provider for testActions
     *
     * @return array
     */
    public function actionsDataProvider()
    {
        return array(
            'Start only once' => array(
                'actions' => array(
                    array('start', 'timer1', 'time' => 25, 'realMemory' => 1500, 'emallocMemory' => 10)
                ),
                'expected' => array(
                    'timer1' => array(
                        \Magento\Framework\Profiler\Driver\Standard\Stat::START => 25,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::TIME => 0,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::REALMEM => 0,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::EMALLOC => 0,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::REALMEM_START => 1500,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::EMALLOC_START => 10,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::COUNT => 1
                    )
                )
            ),
            'Start only twice' => array(
                'actions' => array(
                    array('start', 'timer1', 'time' => 25, 'realMemory' => 1500, 'emallocMemory' => 10),
                    array('start', 'timer1', 'time' => 75, 'realMemory' => 2000, 'emallocMemory' => 20)
                ),
                'expected' => array(
                    'timer1' => array(
                        \Magento\Framework\Profiler\Driver\Standard\Stat::START => 75,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::TIME => 0,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::REALMEM => 0,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::EMALLOC => 0,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::REALMEM_START => 2000,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::EMALLOC_START => 20,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::COUNT => 2
                    )
                )
            ),
            'Start and stop consequentially' => array(
                'actions' => array(
                    array('start', 'timer1', 'time' => 25, 'realMemory' => 1500, 'emallocMemory' => 10),
                    array('stop', 'timer1', 'time' => 75, 'realMemory' => 2000, 'emallocMemory' => 20),
                    array('start', 'timer1', 'time' => 200, 'realMemory' => 3000, 'emallocMemory' => 50),
                    array('stop', 'timer1', 'time' => 250, 'realMemory' => 4000, 'emallocMemory' => 80)
                ),
                'expected' => array(
                    'timer1' => array(
                        \Magento\Framework\Profiler\Driver\Standard\Stat::START => false,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::TIME => 100,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::REALMEM => 1500,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::EMALLOC => 40,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::REALMEM_START => 3000,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::EMALLOC_START => 50,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::COUNT => 2
                    )
                )
            ),
            'Start and stop with inner timer' => array(
                'actions' => array(
                    array('start', 'timer1', 'time' => 25, 'realMemory' => 1500, 'emallocMemory' => 10),
                    array('start', 'timer2', 'time' => 50, 'realMemory' => 2000, 'emallocMemory' => 20),
                    array('stop', 'timer2', 'time' => 80, 'realMemory' => 2500, 'emallocMemory' => 25),
                    array('stop', 'timer1', 'time' => 100, 'realMemory' => 4200, 'emallocMemory' => 55)
                ),
                'expected' => array(
                    'timer1' => array(
                        \Magento\Framework\Profiler\Driver\Standard\Stat::START => false,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::TIME => 75,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::REALMEM => 2700,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::EMALLOC => 45,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::REALMEM_START => 1500,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::EMALLOC_START => 10,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::COUNT => 1
                    ),
                    'timer2' => array(
                        \Magento\Framework\Profiler\Driver\Standard\Stat::START => false,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::TIME => 30,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::REALMEM => 500,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::EMALLOC => 5,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::REALMEM_START => 2000,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::EMALLOC_START => 20,
                        \Magento\Framework\Profiler\Driver\Standard\Stat::COUNT => 1
                    )
                )
            )
        );
    }

    /**
     * Test get method with invalid timer id
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Timer "unknown_timer" doesn't exist.
     */
    public function testGetWithInvalidTimer()
    {
        $this->_stat->get('unknown_timer');
    }

    /**
     * Test stop method with invalid timer id
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Timer "unknown_timer" doesn't exist.
     */
    public function testStopWithInvalidTimer()
    {
        $this->_stat->stop('unknown_timer', 1, 2, 3);
    }

    /**
     * Test clear method
     */
    public function testClear()
    {
        $this->_stat->start('timer1', 1, 20, 10);
        $this->_stat->start('timer2', 2, 20, 10);
        $this->_stat->start('timer3', 3, 20, 10);
        $this->assertAttributeCount(3, '_timers', $this->_stat);

        $this->_stat->clear('timer1');
        $this->assertAttributeCount(2, '_timers', $this->_stat);

        $this->_stat->clear();
        $this->assertAttributeEmpty('_timers', $this->_stat);
    }

    /**
     * Test getFilteredTimerIds for sorting
     *
     * @dataProvider timersSortingDataProvider
     * @param array $timers
     * @param array $expectedTimerIds
     */
    public function testTimersSorting($timers, $expectedTimerIds)
    {
        foreach ($timers as $timerData) {
            list($action, $timerId) = $timerData;
            $this->_executeTimerAction($action, $timerId);
        }

        $this->assertEquals($expectedTimerIds, $this->_stat->getFilteredTimerIds());
    }

    /**
     * @return array
     */
    public function timersSortingDataProvider()
    {
        return array(
            'Without sorting' => array(
                'actions' => array(
                    array('start', 'root'),
                    array('start', 'root->init'),
                    array('stop', 'root->init'),
                    array('stop', 'root')
                ),
                'expected' => array('root', 'root->init')
            ),
            'Simple sorting' => array(
                'actions' => array(
                    array('start', 'root'),
                    array('start', 'root->di'),
                    array('stop', 'root->di'),
                    array('start', 'root->init'),
                    array('start', 'root->init->init_stores'),
                    array('start', 'root->init->init_stores->store_collection_load_after'),
                    array('stop', 'root->init->init_stores->store_collection_load_after'),
                    array('stop', 'root->init->init_stores'),
                    array('stop', 'root->init'),
                    array('start', 'root->dispatch'),
                    array('stop', 'root->dispatch'),
                    array('stop', 'root')
                ),
                'expected' => array(
                    'root',
                    'root->di',
                    'root->init',
                    'root->init->init_stores',
                    'root->init->init_stores->store_collection_load_after',
                    'root->dispatch'
                )
            ),
            'Nested sorting' => array(
                'actions' => array(
                    array('start', 'root'),
                    array('start', 'root->init'),
                    array('start', 'root->system'),
                    array('stop', 'root->system'),
                    array('start', 'root->init->init_config'),
                    array('stop', 'root->init->init_config'),
                    array('stop', 'root->init'),
                    array('stop', 'root')
                ),
                'expected' => array('root', 'root->init', 'root->init->init_config', 'root->system')
            )
        );
    }

    /**
     * Test getFilteredTimerIds for filtering
     *
     * @dataProvider timersFilteringDataProvider
     * @param array $timers
     * @param array $thresholds
     * @param string $filterPattern
     * @param array $expectedTimerIds
     */
    public function testTimersFiltering($timers, $thresholds, $filterPattern, $expectedTimerIds)
    {
        foreach ($timers as $timerData) {
            list($action, $timerId, $time, $realMemory, $emallocMemory) = array_pad(array_values($timerData), 5, 0);
            $this->_executeTimerAction($action, $timerId, $time, $realMemory, $emallocMemory);
        }

        $this->assertEquals($expectedTimerIds, $this->_stat->getFilteredTimerIds($thresholds, $filterPattern));
    }

    /**
     * @return array
     */
    public function timersFilteringDataProvider()
    {
        return array(
            'Filtering by pattern' => array(
                'actions' => array(
                    array('start', 'root'),
                    array('start', 'root->init'),
                    array('stop', 'root->init'),
                    array('stop', 'root')
                ),
                'thresholds' => array(),
                'filterPattern' => '/^root$/',
                'expected' => array('root')
            ),
            'Filtering by thresholds' => array(
                'actions' => array(
                    array('start', 'root', 'time' => 0, 'realMemory' => 0, 'emallocMemory' => 0),
                    array('start', 'root->init', 0),
                    array('start', 'root->init->init_cache', 'time' => 50, 'realMemory' => 1000),
                    array('stop', 'root->init->init_cache', 'time' => 100, 'realMemory' => 21000),
                    array('stop', 'root->init', 999),
                    array('stop', 'root', 'time' => 1000, 'realMemory' => 500, 'emallocMemory' => 0)
                ),
                'thresholds' => array(
                    \Magento\Framework\Profiler\Driver\Standard\Stat::TIME => 1000,
                    \Magento\Framework\Profiler\Driver\Standard\Stat::REALMEM => 20000
                ),
                'filterPattern' => null,
                // TIME >= 1000, REALMEM >= 20000
                'expected' => array('root', 'root->init->init_cache')
            )
        );
    }

    /**
     * Test positive cases of fetch method
     *
     * @dataProvider fetchDataProvider
     * @param array $timers
     * @param array $expects
     */
    public function testFetch($timers, $expects)
    {
        foreach ($timers as $timerData) {
            list($action, $timerId, $time, $realMemory, $emallocMemory) = array_pad(array_values($timerData), 5, 0);
            $this->_executeTimerAction($action, $timerId, $time, $realMemory, $emallocMemory);
        }
        foreach ($expects as $expectedData) {
            /** @var bool|int|PHPUnit_Framework_Constraint $expectedValue */
            list($timerId, $key, $expectedValue) = array_values($expectedData);
            if ($expectedValue instanceof \PHPUnit_Framework_Constraint) {
                $expectedValue->evaluate($this->_stat->fetch($timerId, $key));
            } else {
                $this->assertEquals($expectedValue, $this->_stat->fetch($timerId, $key));
            }
        }
    }

    /**
     * @return array
     */
    public function fetchDataProvider()
    {
        return array(
            array(
                'actions' => array(
                    array('start', 'root', 'time' => 0, 'realMemory' => 0, 'emallocMemory' => 0),
                    array('stop', 'root', 'time' => 1000, 'realMemory' => 500, 'emallocMemory' => 10)
                ),
                'expects' => array(
                    array(
                        'timerId' => 'root',
                        'key' => \Magento\Framework\Profiler\Driver\Standard\Stat::START,
                        'expectedValue' => false
                    ),
                    array(
                        'timerId' => 'root',
                        'key' => \Magento\Framework\Profiler\Driver\Standard\Stat::TIME,
                        'expectedValue' => 1000
                    ),
                    array(
                        'timerId' => 'root',
                        'key' => \Magento\Framework\Profiler\Driver\Standard\Stat::REALMEM,
                        'expectedValue' => 500
                    ),
                    array(
                        'timerId' => 'root',
                        'key' => \Magento\Framework\Profiler\Driver\Standard\Stat::EMALLOC,
                        'expectedValue' => 10
                    )
                )
            ),
            array(
                'actions' => array(
                    array('start', 'root', 'time' => 0),
                    array('stop', 'root', 'time' => 10),
                    array('start', 'root', 'time' => 20),
                    array('stop', 'root', 'time' => 30)
                ),
                'expects' => array(
                    array(
                        'timerId' => 'root',
                        'key' => \Magento\Framework\Profiler\Driver\Standard\Stat::AVG,
                        'expectedValue' => 10
                    )
                )
            ),
            array(
                'actions' => array(array('start', 'root', 'time' => 0)),
                'expects' => array(
                    array(
                        'timerId' => 'root',
                        'key' => \Magento\Framework\Profiler\Driver\Standard\Stat::TIME,
                        'expectedValue' => $this->greaterThan(microtime(true))
                    ),
                    array(
                        'timerId' => 'root',
                        'key' => \Magento\Framework\Profiler\Driver\Standard\Stat::ID,
                        'expectedValue' => 'root'
                    )
                )
            )
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedMessage Timer "foo" doesn't exist.
     */
    public function testFetchInvalidTimer()
    {
        $this->_stat->fetch('foo', 'bar');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedMessage Timer "foo" doesn't have value for "bar".
     */
    public function testFetchInvalidKey()
    {
        $this->_stat->start('foo', 0, 0, 0);
        $this->_stat->fetch('foo', 'bar');
    }

    /**
     * Executes stop or start methods on $_stat object
     *
     * @param string $action
     * @param string $timerId
     * @param int $time
     * @param int $realMemory
     * @param int $emallocMemory
     */
    protected function _executeTimerAction($action, $timerId, $time = 0, $realMemory = 0, $emallocMemory = 0)
    {
        switch ($action) {
            case 'start':
                $this->_stat->start($timerId, $time, $realMemory, $emallocMemory);
                break;
            case 'stop':
                $this->_stat->stop($timerId, $time, $realMemory, $emallocMemory);
                break;
            default:
                $this->fail("Unexpected action '{$action}'");
                break;
        }
    }
}
