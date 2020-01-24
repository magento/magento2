<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Console\Test\Unit;

use Magento\Framework\Console\Cli;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *  Test for Magento\Framework\Console\Cli class.
 */
class CliTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Cli
     */
    private $cli;

    /**
     * @var InputInterface|MockObject
     */
    private $inputMock;

    /**
     * @var OutputInterface|MockObject
     */
    private $outputMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
        $this->cli = new Cli();
    }

    /**
     * Make sure exception message is displayed and trace is logged.
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Test message
     */
    public function testDoRunExceptionLogging()
    {
        $e = new \Exception('Test message');
        $this->inputMock->expects($this->once())->method('getFirstArgument')->willThrowException($e);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('error')
            ->with($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        $this->injectMock($loggerMock, 'logger');

        $this->cli->doRun($this->inputMock, $this->outputMock);
    }

    /**
     * Inject mock to Cli property.
     *
     * @param MockObject $mockObject
     * @param string $propertyName
     * @throws \ReflectionException
     */
    private function injectMock(MockObject $mockObject, string $propertyName): void
    {
        $reflection = new \ReflectionClass(Cli::class);
        $reflectionProperty = $reflection->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->cli, $mockObject);
    }
}
