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
 * @package     Magento_Profiler
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test case for Magento_Profiler
 *
 * @group profiler
 */
class Magento_ProfilerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ReflectionProperty
     */
    protected static $_timersProperty;

    public static function setUpBeforeClass()
    {
        self::$_timersProperty = new ReflectionProperty('Magento_Profiler', '_timers');
        self::$_timersProperty->setAccessible(true);
    }

    public static function tearDownAfterClass()
    {
        self::$_timersProperty->setAccessible(false);
    }

    protected function setUp()
    {
        Magento_Profiler::enable();
        /* Profiler measurements fixture */
        self::$_timersProperty->setValue(include __DIR__ . '/Profiler/_files/timers.php');
    }

    protected function tearDown()
    {
        Magento_Profiler::reset();
    }

    public function testEnableDisable()
    {
        Magento_Profiler::start('another_root_level_timer');
        Magento_Profiler::stop('another_root_level_timer');
        Magento_Profiler::disable();
        Magento_Profiler::start('this_timer_should_be_ignored');
        Magento_Profiler::stop('this_timer_should_be_ignored');
        Magento_Profiler::enable();
        Magento_Profiler::start('another_root_level_timer');
        Magento_Profiler::start('another_nested_timer');
        Magento_Profiler::stop('another_nested_timer');
        Magento_Profiler::stop('another_root_level_timer');
        $expectedTimers = array(
            'some_root_timer',
            'some_root_timer->some_nested_timer',
            'some_root_timer->some_nested_timer->some_deeply_nested_timer',
            'one_more_root_timer',
            'another_root_level_timer',
            'another_root_level_timer->another_nested_timer',
        );
        $actualTimers = Magento_Profiler::getTimers();
        $this->assertEquals($expectedTimers, $actualTimers);
    }

    public function testEnableInitOnce()
    {
        $reflectionProperty = new ReflectionProperty('Magento_Profiler', '_isInitialized');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(false);
        $reflectionProperty->setAccessible(false);
        /** @var Magento_Profiler|PHPUnit_Framework_MockObject_MockObject $class */
        $class = $this->getMockClass('Magento_Profiler', array('_initialize'));
        $class::staticExpects($this->once())
            ->method('_initialize')
        ;
        $class::enable();
        $class::enable();
    }

    public function testResetProfiler()
    {
        $this->assertNotEmpty(Magento_Profiler::getTimers());
        Magento_Profiler::reset();
        $this->assertEmpty(Magento_Profiler::getTimers());

        Magento_Profiler::start('another_root_level_timer');
        Magento_Profiler::start('another_nested_timer');
        Magento_Profiler::reset();
        Magento_Profiler::start('timer_that_should_be_root_after_reset');
        $this->assertEquals(
            array(
                'timer_that_should_be_root_after_reset',
            ),
            Magento_Profiler::getTimers()
        );
    }

    /**
     * @dataProvider resetTimerDataProvider
     */
    public function testResetTimer($timerId, $fetchKey)
    {
        $this->assertNotEmpty(Magento_Profiler::fetch($timerId, $fetchKey));
        Magento_Profiler::reset($timerId);
        $this->assertEmpty(Magento_Profiler::fetch($timerId, $fetchKey));
    }

    public function resetTimerDataProvider()
    {
        return array(
            'profiler time'    => array('some_root_timer->some_nested_timer', Magento_Profiler::FETCH_TIME),
            'profiler count'   => array('some_root_timer->some_nested_timer', Magento_Profiler::FETCH_COUNT),
            'profiler avg'     => array('some_root_timer->some_nested_timer', Magento_Profiler::FETCH_AVG),
            'profiler emalloc' => array('some_root_timer->some_nested_timer', Magento_Profiler::FETCH_EMALLOC),
            'profiler realmem' => array('some_root_timer->some_nested_timer', Magento_Profiler::FETCH_REALMEM),
        );
    }

    public function testStart()
    {
        Magento_Profiler::start('another_root_level_timer');
        Magento_Profiler::start('another_nested_timer');
        Magento_Profiler::stop('another_nested_timer');
        Magento_Profiler::stop('another_root_level_timer');
        $expectedTimers = array(
            'some_root_timer',
            'some_root_timer->some_nested_timer',
            'some_root_timer->some_nested_timer->some_deeply_nested_timer',
            'one_more_root_timer',
            'another_root_level_timer',
            'another_root_level_timer->another_nested_timer'
        );
        $actualTimers = Magento_Profiler::getTimers();
        $this->assertEquals($expectedTimers, $actualTimers);
    }

    /**
     * @expectedException Varien_Exception
     */
    public function testStartException()
    {
        Magento_Profiler::start('another_root_level_timer->another_nested_timer');
    }

    /**
     * @dataProvider stopDataProvider
     */
    public function testStop(array $stopArgumentSets)
    {
        $stopCallback = array('Magento_Profiler', 'stop');

        Magento_Profiler::start('another_root_level_timer');
        Magento_Profiler::start('another_nested_timer');
        foreach ($stopArgumentSets as $stopArguments) {
            call_user_func_array($stopCallback, $stopArguments);
        }
        Magento_Profiler::start('one_more_root_timer');
        Magento_Profiler::stop('one_more_root_timer');

        $expected = array(
            'some_root_timer',
            'some_root_timer->some_nested_timer',
            'some_root_timer->some_nested_timer->some_deeply_nested_timer',
            'one_more_root_timer',
            'another_root_level_timer',
            'another_root_level_timer->another_nested_timer',
        );

        $actual = Magento_Profiler::getTimers();
        $this->assertEquals($expected, $actual);
    }

    public function stopDataProvider()
    {
        return array(
            'omit timer name' => array(
                array(array(), array())
            ),
            'null timer name' => array(
                array(array(null), array(null))
            ),
            'pass timer name' => array(
                array(array('another_nested_timer'), array('another_root_level_timer'))
            ),
        );
    }

    /**
     * @dataProvider stopExceptionDataProvider
     * @expectedException Varien_Exception
     */
    public function testStopException(array $timersToStart, array $timersToStop)
    {
        foreach ($timersToStart as $timerName) {
            Magento_Profiler::start($timerName);
        }
        foreach ($timersToStop as $timerName) {
            Magento_Profiler::stop($timerName);
        }
    }

    public function stopExceptionDataProvider()
    {
        return array(
            'stop non-started timer' => array(
                array('another_root_level_timer'), array('non_started_timer')
            ),
            'stop order violation' => array(
                array('another_root_level_timer', 'another_nested_timer'), array('another_root_level_timer')
            ),
        );
    }

    /**
     * @dataProvider fetchDataProvider
     */
    public function testFetch($timerId, $fetchKey, $expectedValue)
    {
        $actualValue = Magento_Profiler::fetch($timerId, $fetchKey);
        $this->assertEquals($expectedValue, $actualValue);
    }

    public function fetchDataProvider()
    {
        $timerId = 'some_root_timer->some_nested_timer';
        return array(
            'time'    => array($timerId, Magento_Profiler::FETCH_TIME,    0.08),
            'count'   => array($timerId, Magento_Profiler::FETCH_COUNT,   3),
            'avg'     => array($timerId, Magento_Profiler::FETCH_AVG,     0.08 / 3),
            'emalloc' => array($timerId, Magento_Profiler::FETCH_EMALLOC, 42000000),
            'realmem' => array($timerId, Magento_Profiler::FETCH_REALMEM, 40000000),
        );
    }

    public function testFetchDefaults()
    {
        $timerId = 'some_root_timer->some_nested_timer';
        $expected = Magento_Profiler::fetch($timerId, Magento_Profiler::FETCH_TIME);
        $actual = Magento_Profiler::fetch($timerId);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider fetchExceptionDataProvider
     * @expectedException Varien_Exception
     */
    public function testFetchException($timerId, $fetchKey)
    {
        Magento_Profiler::fetch($timerId, $fetchKey);
    }

    public function fetchExceptionDataProvider()
    {
        return array(
            'non-existing timer id'  => array('some_non_existing_timer_id', Magento_Profiler::FETCH_TIME),
            'non-existing fetch key' => array('some_root_timer', 'some_non_existing_fetch_key'),
        );
    }

    public function testGetTimers()
    {
        $expectedTimers = array(
            'some_root_timer',
            'some_root_timer->some_nested_timer',
            'some_root_timer->some_nested_timer->some_deeply_nested_timer',
            'one_more_root_timer'
        );
        $actualTimers = Magento_Profiler::getTimers();
        $this->assertEquals($expectedTimers, $actualTimers);
    }

    public function testDisplay()
    {
        $profilerOutputOne = $this->getMockForAbstractClass('Magento_Profiler_OutputAbstract');
        $profilerOutputOne->expects($this->exactly(2))
            ->method('display')
        ;
        $profilerOutputTwo = $this->getMockForAbstractClass('Magento_Profiler_OutputAbstract');
        $profilerOutputTwo->expects($this->once())
            ->method('display')
        ;
        Magento_Profiler::registerOutput($profilerOutputOne);
        Magento_Profiler::display();
        Magento_Profiler::disable();
        Magento_Profiler::registerOutput($profilerOutputTwo);
        Magento_Profiler::display();
    }
}
