<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Test class for \Magento\Framework\Profiler\Driver\Standard\Output\Factory
 */
namespace Magento\Framework\Profiler\Test\Unit\Driver\Standard\Output;

use Magento\Framework\Profiler\Driver\Standard\Output\Factory;
use Magento\Framework\Profiler\Driver\Standard\OutputInterface;
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
    protected $_defaultOutputPrefix = 'Magento_Framework_Profiler_Driver_Standard_Output_Test_';

    /**
     * @var string
     */
    protected $_defaultOutputType = 'default';

    protected function setUp(): void
    {
        $this->_factory = new Factory(
            $this->_defaultOutputPrefix,
            $this->_defaultOutputType
        );
    }

    public function testConstructor()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $this->assertAttributeEquals($this->_defaultOutputPrefix, '_defaultOutputPrefix', $this->_factory);
        $this->assertAttributeEquals($this->_defaultOutputType, '_defaultOutputType', $this->_factory);
    }

    public function testDefaultConstructor()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $factory = new Factory();
        $this->assertAttributeNotEmpty('_defaultOutputPrefix', $factory);
        $this->assertAttributeNotEmpty('_defaultOutputType', $factory);
    }

    /**
     * @dataProvider createDataProvider
     * @param array|string $configData
     * @param string $expectedClass
     */
    public function testCreate($configData, $expectedClass)
    {
        if (isset($configData['type']) && is_callable($configData['type'])) {
            $configData['type'] = $configData['type']($this);
        }

        if (is_callable($expectedClass)) {
            $expectedClass = $expectedClass($this);
        }

        $driver = $this->_factory->create($configData);
        $this->assertInstanceOf($expectedClass, $driver);
        $this->assertInstanceOf(OutputInterface::class, $driver);
    }

    /**
     * @return array
     */
    public static function createDataProvider(): array
    {
        return [
            'Prefix and concrete type' => [
                ['type' => 'test'],
                static fn(self $testCase) => $testCase->getOutputClassTestMock()
            ],
            'Prefix and default type' => [
                [],
                static fn(self $testCase) => $testCase->getOutputClassDefaultMock()
            ],
            'Concrete class' => [
                ['type' => 'Magento_Framework_Profiler_Driver_Standard_Output_Test_Test_Foo'],
                static fn(self $testCase) => $testCase->getOutputClassTestMock(
                    'Magento_Framework_Profiler_Driver_Standard_Output_Test_Test_Foo'
                )
            ],
        ];
    }

    /**
     * @return array
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function getOutputClassDefaultMock(
        $mockClass = 'Magento_Framework_Profiler_Driver_Standard_Output_Test_Default'
    ): string {
        $defaultOutputClassMock = $this->getMockBuilder(OutputInterface::class)
            ->setMockClassName($mockClass)
            ->getMock();

        return get_class($defaultOutputClassMock);
    }

    public function getOutputClassTestMock(
        $mockClass = 'Magento_Framework_Profiler_Driver_Standard_Output_Test_Test'
    ): string {
        $testOutputClassMock = $this->getMockBuilder(OutputInterface::class)
            ->setMockClassName($mockClass)
            ->getMock();

        return get_class($testOutputClassMock);
    }

    public function testCreateUndefinedClass()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            sprintf(
                'Cannot create standard driver output, class "%s" doesn\'t exist.',
                'Magento_Framework_Profiler_Driver_Standard_Output_Test_Baz'
            )
        );
        $this->_factory->create(['type' => 'baz']);
    }

    public function testCreateInvalidClass()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'Output class "stdClass" must implement \Magento\Framework\Profiler\Driver\Standard\OutputInterface.'
        );
        $this->_factory->create(['type' => 'stdClass']);
    }
}
