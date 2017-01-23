<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\AnalyticsConnector;
use Magento\Framework\ObjectManagerInterface;
use Magento\Analytics\Model\AnalyticsConnector\SignUpCommand;

/**
 * Class SignUpCommandTest
 */
class AnalyticsConnectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var AnalyticsConnector
     */
    private $analyticsConnector;

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
        $this->analyticsConnector = new AnalyticsConnector($this->commands, $this->objectManagerMock);
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
        $this->assertTrue($this->analyticsConnector->execute($commandName));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NotFoundException
     */
    public function testExecuteCommandNotFound()
    {
        $commandName = 'register';
        $this->analyticsConnector->execute($commandName);
    }
}
