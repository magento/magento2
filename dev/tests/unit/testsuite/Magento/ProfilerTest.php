<?php
/**
 * Unit Test for \Magento\Profiler
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento;

class ProfilerTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        \Magento\Profiler::reset();
    }

    public function testEnable()
    {
        \Magento\Profiler::enable();
        $this->assertTrue(\Magento\Profiler::isEnabled());
    }

    public function testDisable()
    {
        \Magento\Profiler::disable();
        $this->assertFalse(\Magento\Profiler::isEnabled());
    }

    public function testSetDefaultTags()
    {
        $expected = array('some_key' => 'some_value');
        \Magento\Profiler::setDefaultTags($expected);
        $this->assertAttributeEquals($expected, '_defaultTags', 'Magento\Profiler');
    }

    public function testAddTagFilter()
    {
        \Magento\Profiler::addTagFilter('tag1', 'value_1.1');
        \Magento\Profiler::addTagFilter('tag2', 'value_2.1');
        \Magento\Profiler::addTagFilter('tag1', 'value_1.2');

        $expected = array(
            'tag1' => array('value_1.1', 'value_1.2'),
            'tag2' => array('value_2.1'),
        );
        $this->assertAttributeEquals($expected, '_tagFilters', 'Magento\Profiler');
        $this->assertAttributeEquals(true, '_hasTagFilters', 'Magento\Profiler');
    }

    public function testAdd()
    {
        $mock = $this->_getDriverMock();
        \Magento\Profiler::add($mock);

        $this->assertTrue(\Magento\Profiler::isEnabled());

        $expected = array($mock);
        $this->assertAttributeEquals($expected, '_drivers', 'Magento\Profiler');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getDriverMock()
    {
        return $this->getMockBuilder('Magento\Profiler\DriverInterface')
            ->setMethods(array('start', 'stop', 'clear'))
            ->getMockForAbstractClass();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Timer name must not contain a nesting separator.
     */
    public function testStartException()
    {
        \Magento\Profiler::enable();
        \Magento\Profiler::start('timer ' . \Magento\Profiler::NESTING_SEPARATOR . ' name');
    }

    public function testDisabledProfiler()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->never())
            ->method('start');
        $driver->expects($this->never())
            ->method('stop');

        \Magento\Profiler::add($driver);
        \Magento\Profiler::disable();
        \Magento\Profiler::start('test');
        \Magento\Profiler::stop('test');
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

        \Magento\Profiler::add($driver);
        \Magento\Profiler::start('root_level_timer');
        \Magento\Profiler::stop('root_level_timer');
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

        \Magento\Profiler::add($driver);
        \Magento\Profiler::start('root_level_timer');
        \Magento\Profiler::start('some_other_timer');
        \Magento\Profiler::stop('some_other_timer');
        \Magento\Profiler::stop('root_level_timer');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Timer "unknown" has not been started.
     */
    public function testStopExceptionUnknown()
    {
        \Magento\Profiler::enable();
        \Magento\Profiler::start('timer');
        \Magento\Profiler::stop('unknown');
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

        \Magento\Profiler::add($driver);
        \Magento\Profiler::start('timer1');
        \Magento\Profiler::start('timer2');
        \Magento\Profiler::start('timer1');
        \Magento\Profiler::start('timer3');
        \Magento\Profiler::stop('timer1');
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

        \Magento\Profiler::add($driver);
        \Magento\Profiler::start('timer1');
        \Magento\Profiler::start('timer1');
        \Magento\Profiler::stop('timer1');
        \Magento\Profiler::stop('timer1');
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

        \Magento\Profiler::add($driver);
        \Magento\Profiler::start('root_level_timer');
        \Magento\Profiler::stop();
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

        \Magento\Profiler::add($driver);
        \Magento\Profiler::setDefaultTags(array('default_tag' => 'default'));
        \Magento\Profiler::start('root_level_timer');
        \Magento\Profiler::start('some_other_timer', array('type' => 'test'));
    }

    public function testClearTimer()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->once())
            ->method('clear')
            ->with('timer');

        \Magento\Profiler::add($driver);
        \Magento\Profiler::clear('timer');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Timer name must not contain a nesting separator.
     */
    public function testClearException()
    {
        \Magento\Profiler::enable();
        \Magento\Profiler::clear('timer ' . \Magento\Profiler::NESTING_SEPARATOR . ' name');
    }

    public function testResetProfiler()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->once())
            ->method('clear')
            ->with(null);

        \Magento\Profiler::add($driver);
        \Magento\Profiler::reset();

        $this->assertAttributeEquals(array(), '_currentPath', 'Magento\Profiler');
        $this->assertAttributeEquals(array(), '_tagFilters', 'Magento\Profiler');
        $this->assertAttributeEquals(array(), '_defaultTags', 'Magento\Profiler');
        $this->assertAttributeEquals(array(), '_drivers', 'Magento\Profiler');
        $this->assertAttributeEquals(false, '_hasTagFilters', 'Magento\Profiler');
        $this->assertAttributeEquals(0, '_pathCount', 'Magento\Profiler');
        $this->assertAttributeEquals(array(), '_pathIndex', 'Magento\Profiler');
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

        \Magento\Profiler::add($driver);
        \Magento\Profiler::addTagFilter('type', 'test');
        \Magento\Profiler::start($timerName, $tags);
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

        \Magento\Profiler::add($driver);
        \Magento\Profiler::addTagFilter('type', 'test');
        \Magento\Profiler::start($timerName, $tags);
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

    public function testApplyConfig()
    {
        $mockDriver = $this->getMock('Magento\Profiler\DriverInterface');
        $driverConfig = array(
            'type' => 'foo'
        );
        $mockDriverFactory = $this->getMockBuilder('Magento\Profiler\Driver\Factory')
            ->disableOriginalConstructor()
            ->getMock();
        $config = array(
            'drivers' => array($driverConfig),
            'driverFactory' => $mockDriverFactory,
            'tagFilters' => array(
                'tagName' => 'tagValue'
            )
        );

        $mockDriverFactory->expects($this->once())
            ->method('create')
            ->with($driverConfig)
            ->will($this->returnValue($mockDriver));

        \Magento\Profiler::applyConfig($config, '');
        $this->assertAttributeEquals(array(
            $mockDriver
        ), '_drivers', 'Magento\Profiler');
        $this->assertAttributeEquals(array(
            'tagName' => array(
                'tagValue'
            )
        ), '_tagFilters', 'Magento\Profiler');
        $this->assertAttributeEquals(true, '_enabled', 'Magento\Profiler');
    }

    /**
     * @dataProvider parseConfigDataProvider
     * @param array $data
     * @param boolean $isAjax
     * @param array $expected
     */
    public function testParseConfig($data, $isAjax, $expected)
    {
        $method = new \ReflectionMethod('Magento\Profiler', '_parseConfig');
        $method->setAccessible(true);
        $this->assertEquals($expected, $method->invoke(null, $data, '', $isAjax));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function parseConfigDataProvider()
    {
        $driverFactory = new \Magento\Profiler\Driver\Factory();
        $otherDriverFactory = $this->getMock('Magento\Profiler\Driver\Factory');
        return array(
            'Empty configuration' => array(
                array(),
                false,
                array(
                    'driverConfigs' => array(),
                    'driverFactory' => $driverFactory,
                    'tagFilters' => array(),
                    'baseDir' => null,
                )
            ),
            'Full configuration' => array(
                array(
                    'drivers' => array(
                        array(
                            'type' => 'foo'
                        )
                    ),
                    'driverFactory' => $otherDriverFactory,
                    'tagFilters' => array('key' => 'value'),
                    'baseDir' => '/custom/base/dir'
                ),
                false,
                array(
                    'driverConfigs' => array(
                        array(
                            'type' => 'foo',
                            'baseDir' => '/custom/base/dir'
                        )
                    ),
                    'driverFactory' => $otherDriverFactory,
                    'tagFilters' => array('key' => 'value'),
                    'baseDir' => '/custom/base/dir',
                )
            ),
            'Driver configuration with type in index' => array(
                array(
                    'drivers' => array(
                        'foo' => 1
                    )
                ),
                false,
                array(
                    'driverConfigs' => array(array(
                        'type' => 'foo'
                    )),
                    'driverFactory' => $driverFactory,
                    'tagFilters' => array(),
                    'baseDir' => null,
                )
            ),
            'Driver configuration with type in value' => array(
                array(
                    'drivers' => array(
                        'foo'
                    )
                ),
                false,
                array(
                    'driverConfigs' => array(array(
                        'type' => 'foo'
                    )),
                    'driverFactory' => $driverFactory,
                    'tagFilters' => array(),
                    'baseDir' => null,
                )
            ),
            'Driver ignored configuration' => array(
                array(
                    'drivers' => array(
                        'foo' => 0
                    )
                ),
                false,
                array(
                    'driverConfigs' => array(),
                    'driverFactory' => $driverFactory,
                    'tagFilters' => array(),
                    'baseDir' => null,
                )
            ),
            'Ajax call' => array(
                1,
                true,
                array(
                    'driverConfigs' => array(array(
                        'output' => 'firebug'
                    )),
                    'driverFactory' => $driverFactory,
                    'tagFilters' => array(),
                    'baseDir' => '',
                )
            ),
            'Non ajax call' => array(
                1,
                false,
                array(
                    'driverConfigs' => array(array(
                        'output' => 'html'
                    )),
                    'driverFactory' => $driverFactory,
                    'tagFilters' => array(),
                    'baseDir' => '',
                )
            )
        );
    }
}
