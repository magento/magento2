<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Message\Test\Unit;

use Magento\Framework\Message\Factory;
use Magento\Framework\Message\Success;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var MockObject
     */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->factory = new Factory(
            $this->objectManagerMock
        );
    }

    public function testCreateWithWrongTypeException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Wrong message type');
        $this->objectManagerMock->expects($this->never())->method('create');
        $this->factory->create('type', 'text');
    }

    public function testCreateWithWrongInterfaceImplementation()
    {
        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage(
            'Magento\Framework\Message\Error doesn\'t implement \Magento\Framework\Message\MessageInterface'
        );
        $messageMock = new \stdClass();
        $type = 'error';
        $className = 'Magento\\Framework\\Message\\' . ucfirst($type);
        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with($className, ['text' => 'text'])
            ->willReturn($messageMock);
        $this->factory->create($type, 'text');
    }

    public function testSuccessfulCreateMessage()
    {
        $messageMock = $this->createMock(Success::class);
        $type = 'success';
        $className = 'Magento\\Framework\\Message\\' . ucfirst($type);
        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with($className, ['text' => 'text'])
            ->willReturn($messageMock);
        $this->assertEquals($messageMock, $this->factory->create($type, 'text'));
    }
}
