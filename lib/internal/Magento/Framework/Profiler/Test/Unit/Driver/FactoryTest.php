<?php declare(strict_types=1);
/**
 * Test class for \Magento\Framework\Profiler\Driver\Factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Test\Unit\Driver;

use Magento\Framework\Profiler\Driver\Factory;
use Magento\Framework\Profiler\DriverInterface;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var Factory
     */
    protected $_factory;

    /**
     * @var string
     */
    protected $_defaultDriverPrefix = 'Magento_Framework_Profiler_Driver_Test_';

    /**
     * @var string
     */
    protected $_defaultDriverType = 'default';

    protected function setUp(): void
    {
        $this->_factory = new Factory(
            $this->_defaultDriverPrefix,
            $this->_defaultDriverType
        );
    }

    public function testConstructor()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $this->assertAttributeEquals($this->_defaultDriverPrefix, '_defaultDriverPrefix', $this->_factory);
        $this->assertAttributeEquals($this->_defaultDriverType, '_defaultDriverType', $this->_factory);
    }

    public function testDefaultConstructor()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $factory = new Factory();
        $this->assertAttributeNotEmpty('_defaultDriverPrefix', $factory);
        $this->assertAttributeNotEmpty('_defaultDriverType', $factory);
    }

    /**
     * @dataProvider createDataProvider
     * @param array $config
     * @param string $expectedClass
     */
    public function testCreate($config, $expectedClass)
    {
        $driver = $this->_factory->create($config);
        $this->assertInstanceOf($expectedClass, $driver);
        $this->assertInstanceOf(DriverInterface::class, $driver);
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        $defaultDriverClass = $this->getMockClass(
            DriverInterface::class,
            [],
            [],
            'Magento_Framework_Profiler_Driver_Test_Default'
        );
        $testDriverClass = $this->getMockClass(
            DriverInterface::class,
            [],
            [],
            'Magento_Framework_Profiler_Driver_Test_Test'
        );
        return [
            'Prefix and concrete type' => [['type' => 'test'], $testDriverClass],
            'Prefix and default type' => [[], $defaultDriverClass],
            'Concrete class' => [['type' => $testDriverClass], $testDriverClass]
        ];
    }

    public function testCreateUndefinedClass()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'Cannot create profiler driver, class "Magento_Framework_Profiler_Driver_Test_Baz" doesn\'t exist.'
        );
        $this->_factory->create(['type' => 'baz']);
    }

    public function testCreateInvalidClass()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'Driver class "stdClass" must implement \Magento\Framework\Profiler\DriverInterface.'
        );
        $this->_factory->create(['type' => 'stdClass']);
    }
}
