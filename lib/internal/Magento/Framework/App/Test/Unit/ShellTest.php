<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\Shell;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Shell\Driver;
use Magento\Framework\Shell\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ShellTest extends TestCase
{
    /** @var  MockObject|LoggerInterface */
    private $loggerMock;

    /** @var  MockObject|Driver */
    private $driverMock;

    /** @var  Shell */
    private $model;

    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->driverMock = $this->getMockBuilder(Driver::class)
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
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage("Command returned non-zero exit code:\n`$command`");
        $this->model->execute($command, []);
    }
}
