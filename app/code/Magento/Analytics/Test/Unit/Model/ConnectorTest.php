<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\Connector;
use Magento\Analytics\Model\Connector\SignUpCommand;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConnectorTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var SignUpCommand|MockObject
     */
    private $signUpCommandMock;

    /**
     * @var array
     */
    private $commands;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->signUpCommandMock = $this->createMock(SignUpCommand::class);
        $this->commands = ['signUp' => SignUpCommand::class];
        $this->connector = new Connector($this->commands, $this->objectManagerMock);
    }

    public function testExecute()
    {
        $commandName = 'signUp';
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($this->commands[$commandName])
            ->willReturn($this->signUpCommandMock);
        $this->signUpCommandMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->assertTrue($this->connector->execute($commandName));
    }

    /**
     * Executing non-existing command
     *
     * @return void
     */
    public function testExecuteCommandNotFound(): void
    {
        $this->expectException('Magento\Framework\Exception\NotFoundException');
        $this->expectExceptionMessage('Command "register" was not found.');
        $commandName = 'register';
        $this->connector->execute($commandName);
    }
}
