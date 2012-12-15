<?php
/**
 * Unit Test for Magento_Profiler
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_ProfilerTest extends PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        Magento_Profiler::reset();
    }

    public function testEnable()
    {
        Magento_Profiler::enable();
        $this->assertTrue(Magento_Profiler::isEnabled());
    }

    public function testDisable()
    {
        Magento_Profiler::disable();
        $this->assertFalse(Magento_Profiler::isEnabled());
    }

    public function testSetDefaultTags()
    {
        $expected = array('tenantId' => '12345');
        Magento_Profiler::setDefaultTags($expected);
        $this->assertAttributeEquals($expected, '_defaultTags', 'Magento_Profiler');
    }

    public function testAddTagFilter()
    {
        Magento_Profiler::addTagFilter('tag1', 'value_1.1');
        Magento_Profiler::addTagFilter('tag2', 'value_2.1');
        Magento_Profiler::addTagFilter('tag1', 'value_1.2');

        $expected = array(
            'tag1' => array('value_1.1', 'value_1.2'),
            'tag2' => array('value_2.1'),
        );
        $this->assertAttributeEquals($expected, '_tagFilters', 'Magento_Profiler');
        $this->assertAttributeEquals(true, '_hasTagFilters', 'Magento_Profiler');
    }

    public function testAdd()
    {
        $mock = $this->_getDriverMock();
        Magento_Profiler::add($mock);

        $this->assertTrue(Magento_Profiler::isEnabled());

        $expected = array(
            get_class($mock) => $mock
        );
        $this->assertAttributeEquals($expected, '_drivers', 'Magento_Profiler');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getDriverMock()
    {
        return $this->getMockBuilder('Magento_Profiler_DriverInterface')
            ->setMethods(array('start', 'stop', 'clear'))
            ->getMockForAbstractClass();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Timer name must not contain a nesting separator.
     */
    public function testStartException()
    {
        Magento_Profiler::enable();
        Magento_Profiler::start('timer ' . Magento_Profiler::NESTING_SEPARATOR . ' name');
    }

    public function testDisabledProfiler()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->never())
            ->method('start');
        $driver->expects($this->never())
            ->method('stop');

        Magento_Profiler::add($driver);
        Magento_Profiler::disable();
        Magento_Profiler::start('test');
        Magento_Profiler::stop('test');
    }

    public function testStartStopSimple()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->once())
            ->method('start')
            ->with('root_level_timer', null);
        $driver->expects($this->once())
            ->method('stop')
            ->with('root_level_timer');

        Magento_Profiler::add($driver);
        Magento_Profiler::start('root_level_timer');
        Magento_Profiler::stop('root_level_timer');
    }

    public function testStartNested()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->at(0))
            ->method('start')
            ->with('root_level_timer', null);
        $driver->expects($this->at(1))
            ->method('start')
            ->with('root_level_timer->some_other_timer', null);

        $driver->expects($this->at(2))
            ->method('stop')
            ->with('root_level_timer->some_other_timer');
        $driver->expects($this->at(3))
            ->method('stop')
            ->with('root_level_timer');

        Magento_Profiler::add($driver);
        Magento_Profiler::start('root_level_timer');
        Magento_Profiler::start('some_other_timer');
        Magento_Profiler::stop('some_other_timer');
        Magento_Profiler::stop('root_level_timer');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Timer "unknown" has not been started.
     */
    public function testStopExceptionUnknown()
    {
        Magento_Profiler::enable();
        Magento_Profiler::start('timer');
        Magento_Profiler::stop('unknown');
    }

    public function testStopOrder()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->at(0))
            ->method('start')
            ->with('timer1', null);
        $driver->expects($this->at(1))
            ->method('start')
            ->with('timer1->timer2', null);
        $driver->expects($this->at(2))
            ->method('start')
            ->with('timer1->timer2->timer1', null);
        $driver->expects($this->at(3))
            ->method('start')
            ->with('timer1->timer2->timer1->timer3', null);

        $driver->expects($this->at(4))
            ->method('stop')
            ->with('timer1->timer2->timer1->timer3');
        $driver->expects($this->at(5))
            ->method('stop')
            ->with('timer1->timer2->timer1');

        $driver->expects($this->exactly(4))
            ->method('start');
        $driver->expects($this->exactly(2))
            ->method('stop');

        Magento_Profiler::add($driver);
        Magento_Profiler::start('timer1');
        Magento_Profiler::start('timer2');
        Magento_Profiler::start('timer1');
        Magento_Profiler::start('timer3');
        Magento_Profiler::stop('timer1');
    }

    public function testStopSameName()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->at(0))
            ->method('start')
            ->with('timer1', null);
        $driver->expects($this->at(1))
            ->method('start')
            ->with('timer1->timer1', null);

        $driver->expects($this->at(2))
            ->method('stop')
            ->with('timer1->timer1');
        $driver->expects($this->at(3))
            ->method('stop')
            ->with('timer1');

        Magento_Profiler::add($driver);
        Magento_Profiler::start('timer1');
        Magento_Profiler::start('timer1');
        Magento_Profiler::stop('timer1');
        Magento_Profiler::stop('timer1');
    }

    public function testStopLatest()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->at(0))
            ->method('start')
            ->with('root_level_timer', null);

        $driver->expects($this->at(1))
            ->method('stop')
            ->with('root_level_timer');

        Magento_Profiler::add($driver);
        Magento_Profiler::start('root_level_timer');
        Magento_Profiler::stop();
    }

    public function testTags()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->at(0))
           ->method('start')
           ->with('root_level_timer', array('default_tag' => 'default'));
        $driver->expects($this->at(1))
            ->method('start')
            ->with('root_level_timer->some_other_timer', array('default_tag' => 'default', 'type' => 'test'));

        Magento_Profiler::add($driver);
        Magento_Profiler::setDefaultTags(array('default_tag' => 'default'));
        Magento_Profiler::start('root_level_timer');
        Magento_Profiler::start('some_other_timer', array('type' => 'test'));
    }

    public function testClearTimer()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->once())
            ->method('clear')
            ->with('timer');

        Magento_Profiler::add($driver);
        Magento_Profiler::clear('timer');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Timer name must not contain a nesting separator.
     */
    public function testClearException()
    {
        Magento_Profiler::enable();
        Magento_Profiler::clear('timer ' . Magento_Profiler::NESTING_SEPARATOR . ' name');
    }

    public function testResetProfiler()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->once())
            ->method('clear')
            ->with(null);

        Magento_Profiler::add($driver);
        Magento_Profiler::reset();

        $this->assertAttributeEquals(array(), '_currentPath', 'Magento_Profiler');
        $this->assertAttributeEquals(array(), '_tagFilters', 'Magento_Profiler');
        $this->assertAttributeEquals(array(), '_defaultTags', 'Magento_Profiler');
        $this->assertAttributeEquals(array(), '_drivers', 'Magento_Profiler');
        $this->assertAttributeEquals(false, '_hasTagFilters', 'Magento_Profiler');
        $this->assertAttributeEquals(0, '_pathCount', 'Magento_Profiler');
        $this->assertAttributeEquals(array(), '_pathIndex', 'Magento_Profiler');
    }

    /**
     * @param string $timerName
     * @param array $tags
     * @dataProvider skippedFilterDataProvider
     */
    public function testTagFilterSkip($timerName, array $tags = null)
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->never())
            ->method('start');

        Magento_Profiler::add($driver);
        Magento_Profiler::addTagFilter('type', 'test');
        Magento_Profiler::start($timerName, $tags);
    }

    /**
     * @return array
     */
    public function skippedFilterDataProvider()
    {
        return array(
            'no tags' => array('timer', null),
            'no expected tags' => array('timer', array('tag' => 'value')),
            'no expected tag value' => array('timer', array('type' => 'db')),
        );
    }

    /**
     * @param string $timerName
     * @param array $tags
     * @dataProvider passedFilterDataProvider
     */
    public function testTagFilterPass($timerName, array $tags = null)
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->once())
            ->method('start')
            ->with($timerName, $tags);

        Magento_Profiler::add($driver);
        Magento_Profiler::addTagFilter('type', 'test');
        Magento_Profiler::start($timerName, $tags);
    }

    /**
     * @return array
     */
    public function passedFilterDataProvider()
    {
        return array(
            'one expected tag' => array('timer', array('type' => 'test')),
            'more than one tag with expected' => array('timer', array('tag' => 'value', 'type' => 'test')),
        );
    }
}
