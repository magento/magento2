<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Message\Test\Unit;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Message\Factory
     */
    protected $factory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->factory = new \Magento\Framework\Message\Factory(
            $this->objectManagerMock
        );
    }

    /**
     */
    public function testCreateWithWrongTypeException()
    {
        $this->expectException(\InvalidArgumentException::class);
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
        $messageMock = $this->createMock(\Magento\Framework\Message\Success::class);
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
