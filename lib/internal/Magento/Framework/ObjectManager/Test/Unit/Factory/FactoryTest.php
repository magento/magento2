<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Test\Unit\Factory;

use Magento\Framework\ObjectManager\FactoryInterface;
use Magento\Framework\ObjectManager\Config\Config;
use Magento\Framework\ObjectManager\Factory\Dynamic\Developer;
use Magento\Framework\ObjectManager\ObjectManager;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->config = new Config();
        $this->factory = new Developer($this->config);
        $this->objectManager = new ObjectManager($this->factory, $this->config);
        $this->factory->setObjectManager($this->objectManager);
    }

    public function testCreateNoArgs()
    {
        $this->assertInstanceOf('StdClass', $this->factory->create('StdClass'));
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Invalid parameter configuration provided for $firstParam argument
     */
    public function testResolveArgumentsException()
    {
        $configMock = $this->getMock('\Magento\Framework\ObjectManager\Config\Config', [], [], '', false);
        $configMock->expects($this->once())->method('getArguments')
            ->will($this->returnValue([
                'firstParam' => 1,
            ]));

        $definitionsMock = $this->getMock('Magento\Framework\ObjectManager\DefinitionInterface');
        $definitionsMock->expects($this->once())->method('getParameters')
            ->will($this->returnValue([[
                'firstParam', 'string', true, 'default_val',
            ]]));

        $this->factory = new Developer(
            $configMock,
            null,
            $definitionsMock
        );
        $this->objectManager = new ObjectManager($this->factory, $this->config);
        $this->factory->setObjectManager($this->objectManager);
        $this->factory->create('Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\OneScalar', ['foo' => 'bar']);
    }

    public function testCreateOneArg()
    {
        /** @var \Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\OneScalar $result */
        $result = $this->factory->create(
            'Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\OneScalar',
            ['foo' => 'bar']
        );
        $this->assertInstanceOf('\Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\OneScalar', $result);
        $this->assertEquals('bar', $result->getFoo());
    }

    public function testCreateWithInjectable()
    {
        // let's imitate that One is injectable by providing DI configuration for it
        $this->config->extend(
            [
                'Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\OneScalar' => [
                    'arguments' => ['foo' => 'bar'],
                ],
            ]
        );
        /** @var \Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Two $result */
        $result = $this->factory->create('Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Two');
        $this->assertInstanceOf('\Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Two', $result);
        $this->assertInstanceOf('\Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\OneScalar', $result->getOne());
        $this->assertEquals('bar', $result->getOne()->getFoo());
        $this->assertEquals('optional', $result->getBaz());
    }

    /**
     * @param string $startingClass
     * @param string $terminationClass
     * @dataProvider circularDataProvider
     */
    public function testCircular($startingClass, $terminationClass)
    {
        $this->setExpectedException(
            '\LogicException',
            sprintf('Circular dependency: %s depends on %s and vice versa.', $startingClass, $terminationClass)
        );
        $this->factory->create($startingClass);
    }

    /**
     * @return array
     */
    public function circularDataProvider()
    {
        $prefix = 'Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\\';
        return [
            ["{$prefix}CircularOne", "{$prefix}CircularThree"],
            ["{$prefix}CircularTwo", "{$prefix}CircularOne"],
            ["{$prefix}CircularThree", "{$prefix}CircularTwo"]
        ];
    }

    public function testCreateUsingReflection()
    {
        $type = 'Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Polymorphous';
        $definitions = $this->getMock('Magento\Framework\ObjectManager\DefinitionInterface');
        // should be more than defined in "switch" of create() method
        $definitions->expects($this->once())->method('getParameters')->with($type)->will($this->returnValue([
            ['one', null, false, null], ['two', null, false, null], ['three', null, false, null],
            ['four', null, false, null], ['five', null, false, null], ['six', null, false, null],
            ['seven', null, false, null], ['eight', null, false, null], ['nine', null, false, null],
            ['ten', null, false, null],
        ]));
        $factory = new Developer($this->config, null, $definitions);
        $result = $factory->create($type, [
            'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5,
            'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9, 'ten' => 10,
        ]);
        $this->assertSame(10, $result->getArg(9));
    }
}
