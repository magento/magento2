<?php
/**
 * Test class for \Magento\Framework\Profiler\Driver\Factory
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Test\Unit\Driver;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Profiler\Driver\Factory
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

    protected function setUp()
    {
        $this->_factory = new \Magento\Framework\Profiler\Driver\Factory(
            $this->_defaultDriverPrefix,
            $this->_defaultDriverType
        );
    }

    public function testConstructor()
    {
        $this->assertAttributeEquals($this->_defaultDriverPrefix, '_defaultDriverPrefix', $this->_factory);
        $this->assertAttributeEquals($this->_defaultDriverType, '_defaultDriverType', $this->_factory);
    }

    public function testDefaultConstructor()
    {
        $factory = new \Magento\Framework\Profiler\Driver\Factory();
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
        $this->assertInstanceOf('Magento\Framework\Profiler\DriverInterface', $driver);
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        $defaultDriverClass = $this->getMockClass(
            'Magento\Framework\Profiler\DriverInterface',
            [],
            [],
            'Magento_Framework_Profiler_Driver_Test_Default'
        );
        $testDriverClass = $this->getMockClass(
            'Magento\Framework\Profiler\DriverInterface',
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
        $this->setExpectedException(
            'InvalidArgumentException',
            'Cannot create profiler driver, class "Magento_Framework_Profiler_Driver_Test_Baz" doesn\'t exist.'
        );
        $this->_factory->create(['type' => 'baz']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Driver class "stdClass" must implement \Magento\Framework\Profiler\DriverInterface.
     */
    public function testCreateInvalidClass()
    {
        $this->_factory->create(['type' => 'stdClass']);
    }
}
