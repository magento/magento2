<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Test\Unit;

class ShellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Shell\CommandRendererInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandRenderer;

    /**
     * @var \Zend_Log|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    protected function setUp()
    {
        $this->logger = $this->getMockBuilder('Zend_Log')
            ->setMethods(['log'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->commandRenderer = new \Magento\Framework\Shell\CommandRenderer();
    }

    /**
     * Test that a command with input arguments returns an expected result
     *
     * @param \Magento\Framework\Shell $shell
     * @param string $command
     * @param array $commandArgs
     * @param string $expectedResult
     */
    protected function _testExecuteCommand(\Magento\Framework\Shell $shell, $command, $commandArgs, $expectedResult)
    {
        $this->expectOutputString('');
        // nothing is expected to be ever printed to the standard output
        $actualResult = $shell->execute($command, $commandArgs);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @param string $command
     * @param array $commandArgs
     * @param string $expectedResult
     * @dataProvider executeDataProvider
     */
    public function testExecute($command, $commandArgs, $expectedResult)
    {
        $this->_testExecuteCommand(
            new \Magento\Framework\Shell($this->commandRenderer, $this->logger), $command, $commandArgs, $expectedResult
        );
    }

    /**
     * @param string $command
     * @param array $commandArgs
     * @param string $expectedResult
     * @param array $expectedLogRecords
     * @dataProvider executeDataProvider
     */
    public function testExecuteLog($command, $commandArgs, $expectedResult, $expectedLogRecords)
    {
        $quoteChar = substr(escapeshellarg(' '), 0, 1);
        // environment-dependent quote character
        foreach ($expectedLogRecords as $logRecordIndex => $expectedLogMessage) {
            $expectedLogMessage = str_replace('`', $quoteChar, $expectedLogMessage);
            $this->logger->expects($this->at($logRecordIndex))
                ->method('log')
                ->with($expectedLogMessage, \Zend_Log::INFO);
        }
        $this->_testExecuteCommand(
            new \Magento\Framework\Shell($this->commandRenderer, $this->logger),
            $command,
            $commandArgs,
            $expectedResult
        );
    }

    public function executeDataProvider()
    {
        // backtick symbol (`) has to be replaced with environment-dependent quote character
        return [
            'STDOUT' => ['php -r %s', ['echo 27181;'], '27181', ['php -r `echo 27181;` 2>&1', '27181']],
            'STDERR' => [
                'php -r %s',
                ['fwrite(STDERR, 27182);'],
                '27182',
                ['php -r `fwrite(STDERR, 27182);` 2>&1', '27182'],
            ],
            'piping STDERR -> STDOUT' => [
                // intentionally no spaces around the pipe symbol
                'php -r %s|php -r %s',
                ['fwrite(STDERR, 27183);', 'echo fgets(STDIN);'],
                '27183',
                ['php -r `fwrite(STDERR, 27183);` 2>&1|php -r `echo fgets(STDIN);` 2>&1', '27183'],
            ],
            'piping STDERR -> STDERR' => [
                'php -r %s | php -r %s',
                ['fwrite(STDERR, 27184);', 'fwrite(STDERR, fgets(STDIN));'],
                '27184',
                ['php -r `fwrite(STDERR, 27184);` 2>&1 | php -r `fwrite(STDERR, fgets(STDIN));` 2>&1', '27184'],
            ]
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Command returned non-zero exit code:
     * @expectedExceptionCode 0
     */
    public function testExecuteFailure()
    {
        $shell = new \Magento\Framework\Shell($this->commandRenderer, $this->logger);
        $shell->execute('non_existing_command');
    }

    /**
     * @param string $command
     * @param array $commandArgs
     * @param string $expectedError
     * @dataProvider executeDataProvider
     */
    public function testExecuteFailureDetails($command, $commandArgs, $expectedError)
    {
        try {
            /* Force command to return non-zero exit code */
            $commandArgs[count($commandArgs) - 1] .= ' exit(42);';
            $this->testExecute($command, $commandArgs, ''); // no result is expected in a case of a command failure
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->assertInstanceOf('Exception', $e->getPrevious());
            $this->assertEquals($expectedError, $e->getPrevious()->getMessage());
            $this->assertEquals(42, $e->getPrevious()->getCode());
        }
    }
}
