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
    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        Profiler::reset();
    }

    /**
     * @return void
     */
    public function testEnable(): void
    {
        Profiler::enable();
        $this->assertTrue(Profiler::isEnabled());
    }

    /**
     * @return void
     */
    public function testDisable(): void
    {
        Profiler::disable();
        $this->assertFalse(Profiler::isEnabled());
    }

    /**
     * @return void
     */
    public function testSetDefaultTags(): void
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $expected = ['some_key' => 'some_value'];
        Profiler::setDefaultTags($expected);
        $this->assertAttributeEquals($expected, '_defaultTags', Profiler::class);
    }

    /**
     * @return void
     */
    public function testAddTagFilter(): void
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        Profiler::addTagFilter('tag1', 'value_1.1');
        Profiler::addTagFilter('tag2', 'value_2.1');
        Profiler::addTagFilter('tag1', 'value_1.2');

        $expected = ['tag1' => ['value_1.1', 'value_1.2'], 'tag2' => ['value_2.1']];
        $this->assertAttributeEquals($expected, '_tagFilters', Profiler::class);
        $this->assertAttributeEquals(true, '_hasTagFilters', Profiler::class);
    }

    /**
     * @return void
     */
    public function testAdd(): void
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
    protected function _getDriverMock(): MockObject
    {
        return $this->getMockBuilder(DriverInterface::class)
            ->onlyMethods(['start', 'stop', 'clear'])->getMockForAbstractClass();
    }

    /**
     * @return void
     */
    public function testStartException(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Timer name must not contain a nesting separator.');
        Profiler::enable();
        Profiler::start('timer ' . Profiler::NESTING_SEPARATOR . ' name');
    }

    /**
     * @return void
     */
    public function testDisabledProfiler(): void
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->never())->method('start');
        $driver->expects($this->never())->method('stop');

        Profiler::add($driver);
        Profiler::disable();
        Profiler::start('test');
        Profiler::stop('test');
    }

    /**
     * @return void
     */
    public function testStartStopSimple(): void
    {
        $driver = $this->_getDriverMock();
        $driver->expects($this->once())->method('start')->with('root_level_timer', null);
        $driver->expects($this->once())->method('stop')->with('root_level_timer');

        Profiler::add($driver);
        Profiler::start('root_level_timer');
        Profiler::stop('root_level_timer');
    }

    /**
     * @return void
     */
    public function testStartNested(): void
    {
        $driver = $this->_getDriverMock();

        $driver
            ->method('start')
            ->willReturnCallback(
                function ($arg1, $arg2) {
                    if ($arg1 == 'root_level_timer' && $arg2 === null) {
                        return null;
                    } elseif ($arg1 == 'root_level_timer->some_other_timer' && $arg2 === null) {
                        return null;
                    }
                }
            );
        $driver
            ->method('stop')
            ->willReturnCallback(
                function ($arg) {
                    if ($arg == 'root_level_timer->some_other_timer' || $arg == 'root_level_timer') {
                        return null;
                    }
                }
            );

        Profiler::add($driver);
        Profiler::start('root_level_timer');
        Profiler::start('some_other_timer');
        Profiler::stop('some_other_timer');
        Profiler::stop('root_level_timer');
    }

    /**
     * @return void
     */
    public function testStopExceptionUnknown(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Timer "unknown" has not been started.');
        Profiler::enable();
        Profiler::start('timer');
        Profiler::stop('unknown');
    }

    /**
     * @return void
     */
    public function testStopOrder(): void
    {
        $driver = $this->_getDriverMock();

        $driver
            ->method('start')
            ->willReturnCallback(
                function ($arg1, $arg2) {
                    if ($arg1 == 'timer1' && $arg2 == null) {
                        return null;
                    } elseif ($arg1 == 'timer1->timer2' && $arg2 == null) {
                        return null;
                    } elseif ($arg1 == 'timer1->timer2->timer1' && $arg2 == null) {
                        return null;
                    } elseif ($arg1 == 'timer1->timer2->timer1->timer3' && $arg2 == null) {
                        return null;
                    }
                }
            );

        $driver
            ->method('stop')
            ->willReturnCallback(
                function ($arg) {
                    if ($arg == 'timer1->timer2->timer1->timer3' || $arg == 'timer1->timer2->timer1') {
                        return null;
                    }
                }
            );

        $driver->expects($this->exactly(4))->method('start');
        $driver->expects($this->exactly(2))->method('stop');

        Profiler::add($driver);
        Profiler::start('timer1');
        Profiler::start('timer2');
        Profiler::start('timer1');
        Profiler::start('timer3');
        Profiler::stop('timer1');
    }

    /**
     * @return void
     */
    public function testStopSameName(): void
    {
        $driver = $this->_getDriverMock();

        $driver
            ->method('start')
            ->willReturnCallback(
                function ($arg1, $arg2) {
                    if ($arg1 == 'timer1' && $arg2 == null) {
                        return null;
                    } elseif ($arg1 == 'timer1->timer1' && $arg2 == null) {
                        return null;
                    }
                }
            );

        $driver
            ->method('stop')
            ->willReturnCallback(
                function ($arg) {
                    if ($arg == 'timer1->timer1' || $arg == 'timer1') {
                        return null;
                    }
                }
            );

        Profiler::add($driver);
        Profiler::start('timer1');
        Profiler::start('timer1');
        Profiler::stop('timer1');
        Profiler::stop('timer1');
    }

    /**
     * @return void
     */
    public function testStopLatest(): void
    {
        $driver = $this->_getDriverMock();

        $driver
            ->method('start')
            ->with('root_level_timer', null);
        $driver
            ->method('stop')
            ->with('root_level_timer');

        Profiler::add($driver);
        Profiler::start('root_level_timer');
        Profiler::stop();
    }

    /**
     * @return void
     */
    public function testTags(): void
    {
        $driver = $this->_getDriverMock();
        $driver
            ->method('start')
            ->willReturnCallback(
                function ($arg1, $arg2) {
                    if ($arg1 == 'root_level_timer' &&
                        $arg2 == ['default_tag' => 'default']) {
                        return null;
                    } elseif ($arg1 == 'root_level_timer->some_other_timer' &&
                        $arg2 == ['default_tag' => 'default', 'type' => 'test']) {
                        return null;
                    }
                }
            );

        Profiler::add($driver);
        Profiler::setDefaultTags(['default_tag' => 'default']);
        Profiler::start('root_level_timer');
        Profiler::start('some_other_timer', ['type' => 'test']);
    }

    /**
     * @return void
     */
    public function testClearTimer(): void
    {
        $driver = $this->_getDriverMock();
        $driver
            ->method('clear')
            ->with('timer');

        Profiler::add($driver);
        Profiler::clear('timer');
    }

    /**
     * @return void
     */
    public function testClearException(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Timer name must not contain a nesting separator.');
        Profiler::enable();
        Profiler::clear('timer ' . Profiler::NESTING_SEPARATOR . ' name');
    }

    /**
     * @return void
     */
    public function testResetProfiler(): void
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
     *
     * @return void
     * @dataProvider skippedFilterDataProvider
     */
    public function testTagFilterSkip($timerName, ?array $tags = null): void
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
    public static function skippedFilterDataProvider(): array
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
     *
     * @return void
     * @dataProvider passedFilterDataProvider
     */
    public function testTagFilterPass($timerName, ?array $tags = null): void
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
    public static function passedFilterDataProvider(): array
    {
        return [
            'one expected tag' => ['timer', ['type' => 'test']],
            'more than one tag with expected' => ['timer', ['tag' => 'value', 'type' => 'test']]
        ];
    }

    /**
     * @return void
     */
    public function testApplyConfig(): void
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
     * @param array $data
     * @param boolean $isAjax
     * @param array $expected
     *
     * @return void
     * @dataProvider parseConfigDataProvider
     */
    public function testParseConfig($data, $isAjax, $expected): void
    {
        if (!empty($data['driverFactory']) && is_callable($data['driverFactory'])) {
            $data['driverFactory'] = $data['driverFactory']($this);
        }
        if (!empty($expected) && is_callable($expected['driverFactory'])) {
            $expected['driverFactory'] = $expected['driverFactory']($this);
        }
        $method = new \ReflectionMethod(Profiler::class, '_parseConfig');
        $method->setAccessible(true);
        $this->assertEquals($expected, $method->invoke(null, $data, '', $isAjax));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function parseConfigDataProvider(): array
    {
        $driverFactory = new Factory();
        $otherDriverFactory = static fn (self $testCase) => $testCase->createMock(Factory::class);
        return [
            'Empty configuration' => [
                [],
                false,
                [
                    'driverConfigs' => [],
                    'driverFactory' => $driverFactory,
                    'tagFilters' => [],
                    'baseDir' => null
                ]
            ],
            'Full configuration' => [
                [
                    'drivers' => [['type' => 'foo']],
                    'driverFactory' => $otherDriverFactory,
                    'tagFilters' => ['key' => 'value'],
                    'baseDir' => '/custom/base/dir'
                ],
                false,
                [
                    'driverConfigs' => [['type' => 'foo', 'baseDir' => '/custom/base/dir']],
                    'driverFactory' => $otherDriverFactory,
                    'tagFilters' => ['key' => 'value'],
                    'baseDir' => '/custom/base/dir'
                ]
            ],
            'Driver configuration with type in index' => [
                ['drivers' => ['foo' => 1]],
                false,
                [
                    'driverConfigs' => [['type' => 'foo']],
                    'driverFactory' => $driverFactory,
                    'tagFilters' => [],
                    'baseDir' => null
                ]
            ],
            'Driver configuration with type in value' => [
                ['drivers' => ['foo']],
                false,
                [
                    'driverConfigs' => [['type' => 'foo']],
                    'driverFactory' => $driverFactory,
                    'tagFilters' => [],
                    'baseDir' => null
                ]
            ],
            'Driver ignored configuration' => [
                ['drivers' => ['foo' => 0]],
                false,
                [
                    'driverConfigs' => [],
                    'driverFactory' => $driverFactory,
                    'tagFilters' => [],
                    'baseDir' => null
                ]
            ],
            'Non ajax call' => [
                1,
                false,
                [
                    'driverConfigs' => [['output' => 'html']],
                    'driverFactory' => $driverFactory,
                    'tagFilters' => [],
                    'baseDir' => ''
                ]
            ]
        ];
    }
}
