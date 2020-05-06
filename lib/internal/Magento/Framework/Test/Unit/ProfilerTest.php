<?php declare(strict_types=1);
/**
 * Unit Test for \Magento\Framework\Profiler
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit;

use Magento\Framework\Profiler;
use Magento\Framework\Profiler\Driver\Factory;
use Magento\Framework\Profiler\DriverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProfilerTest extends TestCase
{
    protected function tearDown(): void
    {
        Profiler::reset();
    }

    public function testEnable()
    {
        Profiler::enable();
        $this->assertTrue(Profiler::isEnabled());
    }

    public function testDisable()
    {
        Profiler::disable();
        $this->assertFalse(Profiler::isEnabled());
    }

    public function testSetDefaultTags()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $expected = ['some_key' => 'some_value'];
        Profiler::setDefaultTags($expected);
        $this->assertAttributeEquals($expected, '_defaultTags', Profiler::class);
    }

    public function testAddTagFilter()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        Profiler::addTagFilter('tag1', 'value_1.1');
        Profiler::addTagFilter('tag2', 'value_2.1');
        Profiler::addTagFilter('tag1', 'value_1.2');

        $expected = ['tag1' => ['value_1.1', 'value_1.2'], 'tag2' => ['value_2.1']];
        $this->assertAttributeEquals($expected, '_tagFilters', Profiler::class);
        $this->assertAttributeEquals(true, '_hasTagFilters', Profiler::class);
    }

    public function testAdd()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $mock = $this->_getDriverMock();
        Profiler::add($mock);

        $this->assertTrue(Profiler::isEnabled());

        $expected = [$mock];
        $this->assertAttributeEquals($expected, '_drivers', Profiler::class);
    }

    /**
     * @return MockObject
     */
    protected function _getDriverMock()
    {
        return $this->getMockBuilder(
            DriverInterface::class
        )->setMethods(
            ['start', 'stop', 'clear']
        )->getMockForAbstractClass();
    }

    public function testStartException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Timer name must not contain a nesting separator.');
        Profiler::enable();
        Profiler::start('timer ' . Profiler::NESTING_SEPARATOR . ' name');
    }

    public function testDisabledProfiler()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->never())->method('start');
        $driver->expects($this->never())->method('stop');

        Profiler::add($driver);
        Profiler::disable();
        Profiler::start('test');
        Profiler::stop('test');
    }

    public function testStartStopSimple()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->once())->method('start')->with('root_level_timer', null);
        $driver->expects($this->once())->method('stop')->with('root_level_timer');

        Profiler::add($driver);
        Profiler::start('root_level_timer');
        Profiler::stop('root_level_timer');
    }

    public function testStartNested()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->at(0))->method('start')->with('root_level_timer', null);
        $driver->expects($this->at(1))->method('start')->with('root_level_timer->some_other_timer', null);

        $driver->expects($this->at(2))->method('stop')->with('root_level_timer->some_other_timer');
        $driver->expects($this->at(3))->method('stop')->with('root_level_timer');

        Profiler::add($driver);
        Profiler::start('root_level_timer');
        Profiler::start('some_other_timer');
        Profiler::stop('some_other_timer');
        Profiler::stop('root_level_timer');
    }

    public function testStopExceptionUnknown()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Timer "unknown" has not been started.');
        Profiler::enable();
        Profiler::start('timer');
        Profiler::stop('unknown');
    }

    public function testStopOrder()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->at(0))->method('start')->with('timer1', null);
        $driver->expects($this->at(1))->method('start')->with('timer1->timer2', null);
        $driver->expects($this->at(2))->method('start')->with('timer1->timer2->timer1', null);
        $driver->expects($this->at(3))->method('start')->with('timer1->timer2->timer1->timer3', null);

        $driver->expects($this->at(4))->method('stop')->with('timer1->timer2->timer1->timer3');
        $driver->expects($this->at(5))->method('stop')->with('timer1->timer2->timer1');

        $driver->expects($this->exactly(4))->method('start');
        $driver->expects($this->exactly(2))->method('stop');

        Profiler::add($driver);
        Profiler::start('timer1');
        Profiler::start('timer2');
        Profiler::start('timer1');
        Profiler::start('timer3');
        Profiler::stop('timer1');
    }

    public function testStopSameName()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->at(0))->method('start')->with('timer1', null);
        $driver->expects($this->at(1))->method('start')->with('timer1->timer1', null);

        $driver->expects($this->at(2))->method('stop')->with('timer1->timer1');
        $driver->expects($this->at(3))->method('stop')->with('timer1');

        Profiler::add($driver);
        Profiler::start('timer1');
        Profiler::start('timer1');
        Profiler::stop('timer1');
        Profiler::stop('timer1');
    }

    public function testStopLatest()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->at(0))->method('start')->with('root_level_timer', null);

        $driver->expects($this->at(1))->method('stop')->with('root_level_timer');

        Profiler::add($driver);
        Profiler::start('root_level_timer');
        Profiler::stop();
    }

    public function testTags()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->at(0))->method('start')->with('root_level_timer', ['default_tag' => 'default']);
        $driver->expects(
            $this->at(1)
        )->method(
            'start'
        )->with(
            'root_level_timer->some_other_timer',
            ['default_tag' => 'default', 'type' => 'test']
        );

        Profiler::add($driver);
        Profiler::setDefaultTags(['default_tag' => 'default']);
        Profiler::start('root_level_timer');
        Profiler::start('some_other_timer', ['type' => 'test']);
    }

    public function testClearTimer()
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->at(0))->method('clear')->with('timer');

        Profiler::add($driver);
        Profiler::clear('timer');
    }

    public function testClearException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Timer name must not contain a nesting separator.');
        Profiler::enable();
        Profiler::clear('timer ' . Profiler::NESTING_SEPARATOR . ' name');
    }

    public function testResetProfiler()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $driver = $this->_getDriverMock();
        $driver->expects($this->once())->method('clear')->with(null);

        Profiler::add($driver);
        Profiler::reset();

        $this->assertAttributeEquals([], '_currentPath', Profiler::class);
        $this->assertAttributeEquals([], '_tagFilters', Profiler::class);
        $this->assertAttributeEquals([], '_defaultTags', Profiler::class);
        $this->assertAttributeEquals([], '_drivers', Profiler::class);
        $this->assertAttributeEquals(false, '_hasTagFilters', Profiler::class);
        $this->assertAttributeEquals(0, '_pathCount', Profiler::class);
        $this->assertAttributeEquals([], '_pathIndex', Profiler::class);
    }

    /**
     * @param string $timerName
     * @param array $tags
     * @dataProvider skippedFilterDataProvider
     */
    public function testTagFilterSkip($timerName, array $tags = null)
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->never())->method('start');

        Profiler::add($driver);
        Profiler::addTagFilter('type', 'test');
        Profiler::start($timerName, $tags);
    }

    /**
     * @return array
     */
    public function skippedFilterDataProvider()
    {
        return [
            'no tags' => ['timer', null],
            'no expected tags' => ['timer', ['tag' => 'value']],
            'no expected tag value' => ['timer', ['type' => 'db']]
        ];
    }

    /**
     * @param string $timerName
     * @param array $tags
     * @dataProvider passedFilterDataProvider
     */
    public function testTagFilterPass($timerName, array $tags = null)
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->once())->method('start')->with($timerName, $tags);

        Profiler::add($driver);
        Profiler::addTagFilter('type', 'test');
        Profiler::start($timerName, $tags);
    }

    /**
     * @return array
     */
    public function passedFilterDataProvider()
    {
        return [
            'one expected tag' => ['timer', ['type' => 'test']],
            'more than one tag with expected' => ['timer', ['tag' => 'value', 'type' => 'test']]
        ];
    }

    public function testApplyConfig()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $mockDriver = $this->getMockForAbstractClass(DriverInterface::class);
        $driverConfig = ['type' => 'foo'];
        $mockDriverFactory = $this->getMockBuilder(
            Factory::class
        )->disableOriginalConstructor()
            ->getMock();
        $config = [
            'drivers' => [$driverConfig],
            'driverFactory' => $mockDriverFactory,
            'tagFilters' => ['tagName' => 'tagValue'],
        ];

        $mockDriverFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $driverConfig
        )->willReturn(
            $mockDriver
        );

        Profiler::applyConfig($config, '');
        $this->assertAttributeEquals([$mockDriver], '_drivers', Profiler::class);
        $this->assertAttributeEquals(
            ['tagName' => ['tagValue']],
            '_tagFilters',
            Profiler::class
        );
        $this->assertAttributeEquals(true, '_enabled', Profiler::class);
    }

    /**
     * @dataProvider parseConfigDataProvider
     * @param array $data
     * @param boolean $isAjax
     * @param array $expected
     */
    public function testParseConfig($data, $isAjax, $expected)
    {
        $method = new \ReflectionMethod(Profiler::class, '_parseConfig');
        $method->setAccessible(true);
        $this->assertEquals($expected, $method->invoke(null, $data, '', $isAjax));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function parseConfigDataProvider()
    {
        $driverFactory = new Factory();
        $otherDriverFactory = $this->createMock(Factory::class);
        return [
            'Empty configuration' => [
                [],
                false,
                [
                    'driverConfigs' => [],
                    'driverFactory' => $driverFactory,
                    'tagFilters' => [],
                    'baseDir' => null
                ],
            ],
            'Full configuration' => [
                [
                    'drivers' => [['type' => 'foo']],
                    'driverFactory' => $otherDriverFactory,
                    'tagFilters' => ['key' => 'value'],
                    'baseDir' => '/custom/base/dir',
                ],
                false,
                [
                    'driverConfigs' => [['type' => 'foo', 'baseDir' => '/custom/base/dir']],
                    'driverFactory' => $otherDriverFactory,
                    'tagFilters' => ['key' => 'value'],
                    'baseDir' => '/custom/base/dir'
                ],
            ],
            'Driver configuration with type in index' => [
                ['drivers' => ['foo' => 1]],
                false,
                [
                    'driverConfigs' => [['type' => 'foo']],
                    'driverFactory' => $driverFactory,
                    'tagFilters' => [],
                    'baseDir' => null
                ],
            ],
            'Driver configuration with type in value' => [
                ['drivers' => ['foo']],
                false,
                [
                    'driverConfigs' => [['type' => 'foo']],
                    'driverFactory' => $driverFactory,
                    'tagFilters' => [],
                    'baseDir' => null
                ],
            ],
            'Driver ignored configuration' => [
                ['drivers' => ['foo' => 0]],
                false,
                [
                    'driverConfigs' => [],
                    'driverFactory' => $driverFactory,
                    'tagFilters' => [],
                    'baseDir' => null
                ],
            ],
            'Non ajax call' => [
                1,
                false,
                [
                    'driverConfigs' => [['output' => 'html']],
                    'driverFactory' => $driverFactory,
                    'tagFilters' => [],
                    'baseDir' => ''
                ],
            ]
        ];
    }
}
