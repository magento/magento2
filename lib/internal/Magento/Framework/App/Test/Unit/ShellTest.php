<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\Shell;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Shell\Response;

class ShellTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject | \Psr\Log\LoggerInterface */
    private $loggerMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Shell\Driver */
    private $driverMock;

    /** @var  \Magento\Framework\App\Shell */
    private $model;

    public function setUp()
    {
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->driverMock = $this->getMockBuilder(\Magento\Framework\Shell\Driver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Shell(
            $this->driverMock,
            $this->loggerMock
        );
    }

    public function testExecuteSuccess()
    {
        $output = 'success';
        $exitCode = 0;
        $command = 'escaped command';
        $logEntry = $command . PHP_EOL . $output;

        $successfulResponse = new Response(
            [
                'output' => $output,
                'exit_code' => $exitCode,
                'escaped_command' => $command
            ]
        );
        $this->driverMock->expects($this->once())->method('execute')->willReturn($successfulResponse);
        $this->loggerMock->expects($this->once())->method('info')->with($logEntry);
        $this->assertEquals($output, $this->model->execute($command, []));
    }

    public function testExecuteFailure()
    {
        $output = 'failure';
        $exitCode = 1;
        $command = 'escaped command';
        $logEntry = $command . PHP_EOL . $output;

        $response = new Response(
            [
                'output' => $output,
                'exit_code' => $exitCode,
                'escaped_command' => $command
            ]
        );
        $this->driverMock->expects($this->once())->method('execute')->willReturn($response);
        $this->loggerMock->expects($this->once())->method('error')->with($logEntry);
        $this->setExpectedException(LocalizedException::class, "Command returned non-zero exit code:\n`$command`");
        $this->model->execute($command, []);
    }
}
