<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\Connector;
use Magento\Framework\ObjectManagerInterface;
use Magento\Analytics\Model\Connector\SignUpCommand;

/**
 * Class SignUpCommandTest
 */
class ConnectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var SignUpCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    private $signUpCommandMock;

    /**
     * @var array
     */
    private $commands;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->signUpCommandMock = $this->getMockBuilder(SignUpCommand::class)
            ->disableOriginalConstructor()
            ->getMock();
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
     * @expectedException \Magento\Framework\Exception\NotFoundException
     */
    public function testExecuteCommandNotFound()
    {
        $commandName = 'register';
        $this->connector->execute($commandName);
    }
}
