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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ObjectManager\Factory;

use Magento\ObjectManager\Config\Config;
use Magento\ObjectManager\ObjectManager;
use Magento\ObjectManager\Config\Argument\ObjectFactory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $interpreterMock;

    protected function setUp()
    {
        $this->config = new Config();
        $this->objectFactory = new ObjectFactory($this->config);
        $this->interpreterMock = $this->getMockForAbstractClass('\Magento\Data\Argument\InterpreterInterface');
        $this->factory = new Factory($this->config, $this->interpreterMock, $this->objectFactory);
        $this->objectManager = new ObjectManager($this->factory, $this->config);
        $this->objectFactory->setObjectManager($this->objectManager);
    }

    public function testCreateNoArgs()
    {
        $this->assertInstanceOf('StdClass', $this->factory->create('StdClass'));
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Invalid parameter configuration provided for $firstParam argument of Magento\ObjectMan
     */
    public function testResolveArgumentsException()
    {
        $configMock = $this->getMock('\Magento\ObjectManager\Config\Config', array(), array(), '', false);
        $configMock->expects($this->once())->method('getArguments')
            ->will($this->returnValue(array(
                'firstParam' => 1
            )));

        $definitionsMock = $this->getMock('\Magento\ObjectManager\Definition', array(), array(), '', false);
        $definitionsMock->expects($this->once())->method('getParameters')
            ->will($this->returnValue(array(array(
                'firstParam', 'string', true, 'default_val'
            ))));

        $this->factory = new Factory(
            $configMock,
            $this->interpreterMock,
            $this->objectFactory,
            $definitionsMock
        );
        $this->objectManager = new ObjectManager($this->factory, $this->config);
        $this->objectFactory->setObjectManager($this->objectManager);
        $this->factory->create('Magento\ObjectManager\Factory\Fixture\OneScalar', array('foo' => 'bar'));
    }

    public function testCreateOneArg()
    {
        /** @var \Magento\ObjectManager\Factory\Fixture\OneScalar $result */
        $result = $this->factory->create('Magento\ObjectManager\Factory\Fixture\OneScalar', array('foo' => 'bar'));
        $this->assertInstanceOf('\Magento\ObjectManager\Factory\Fixture\OneScalar', $result);
        $this->assertEquals('bar', $result->getFoo());
    }

    public function testCreateWithInjectable()
    {
        // let's imitate that One is injectable by providing DI configuration for it
        $this->config->extend(
            array(
                'Magento\ObjectManager\Factory\Fixture\OneScalar' => array(
                    'arguments' => array('foo' => array('value' => 'bar'))
                )
            )
        );
        $this->interpreterMock
            ->expects($this->once())
            ->method('evaluate')
            ->with(array('value' => 'bar'))
            ->will($this->returnValue('bar'))
        ;
        /** @var \Magento\ObjectManager\Factory\Fixture\Two $result */
        $result = $this->factory->create('Magento\ObjectManager\Factory\Fixture\Two');
        $this->assertInstanceOf('\Magento\ObjectManager\Factory\Fixture\Two', $result);
        $this->assertInstanceOf('\Magento\ObjectManager\Factory\Fixture\OneScalar', $result->getOne());
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
        $prefix = 'Magento\ObjectManager\Factory\Fixture\\';
        return array(
            array("{$prefix}CircularOne", "{$prefix}CircularThree"),
            array("{$prefix}CircularTwo", "{$prefix}CircularOne"),
            array("{$prefix}CircularThree", "{$prefix}CircularTwo")
        );
    }

    public function testCreateUsingReflection()
    {
        $type = 'Magento\ObjectManager\Factory\Fixture\Polymorphous';
        $definitions = $this->getMockForAbstractClass('\Magento\ObjectManager\Definition');
        // should be more than defined in "switch" of create() method
        $definitions->expects($this->once())->method('getParameters')->with($type)->will($this->returnValue(array(
            array('one', 'int', false, null), array('two', 'int', false, null), array('three', 'int', false, null),
            array('four', 'int', false, null), array('five', 'int', false, null), array('six', 'int', false, null),
            array('seven', 'int', false, null), array('eight', 'int', false, null), array('nine', 'int', false, null),
            array('ten', 'int', false, null),
        )));
        $factory = new Factory($this->config, $this->interpreterMock, $this->objectFactory, $definitions);
        $result = $factory->create($type, array(
            'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5,
            'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9, 'ten' => 10,
        ));
        $this->assertSame(10, $result->getArg(9));
    }
}
