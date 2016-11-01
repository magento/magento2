<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Test\Unit;

use Magento\Framework\ObjectManager\Definition\Compiled;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\ObjectManager\DefinitionFactory;
use Magento\Framework\ObjectManager\Definition\Runtime;

class DefinitionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DriverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemDriverMock;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var DefinitionFactory
     */
    private $definitionFactory;

    protected function setUp()
    {
        $this->filesystemDriverMock = $this->getMock(DriverInterface::class);
        $this->serializerMock = $this->getMock(SerializerInterface::class);
        $this->definitionFactory = new DefinitionFactory(
            $this->filesystemDriverMock,
            $this->serializerMock,
            'generation dir'
        );
    }

    public function testCreateClassDefinitionSerialized()
    {
        $serializedDefinitions = 'serialized definitions';
        $definitions = [[], []];
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedDefinitions)
            ->willReturn($definitions);
        $this->assertInstanceOf(
            Compiled::class,
            $this->definitionFactory->createClassDefinition($serializedDefinitions)
        );
    }

    public function testCreateClassDefinitionArray()
    {
        $definitions = [[], []];
        $this->serializerMock->expects($this->never())
            ->method('unserialize');
        $this->assertInstanceOf(
            Compiled::class,
            $this->definitionFactory->createClassDefinition($definitions)
        );
    }

    public function testCreateClassDefinition()
    {
        $this->serializerMock->expects($this->never())
            ->method('unserialize');
        $this->assertInstanceOf(
            Runtime::class,
            $this->definitionFactory->createClassDefinition()
        );
    }
}
