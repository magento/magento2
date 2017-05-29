<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Console\Command;

use Magento\Config\Model\Config\Export\Comment;
use Magento\Deploy\Console\Command\App\ApplicationDumpCommand;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Framework\App\Config\Reader\Source\SourceInterface;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test command for dump application state
 */
class ApplicationDumpCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $input;

    /**
     * @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $output;

    /**
     * @var Writer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $writer;

    /**
     * @var SourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $source;

    /**
     * @var SourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceEnv;

    /**
     * @var Hash|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configHashMock;

    /**
     * @var Comment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentMock;

    /**
     * @var ApplicationDumpCommand
     */
    private $command;

    public function setUp()
    {
        $this->configHashMock = $this->getMockBuilder(Hash::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->input = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();
        $this->output = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
        $this->writer = $this->getMockBuilder(Writer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->source = $this->getMockBuilder(SourceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sourceEnv = $this->getMockBuilder(SourceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->commentMock = $this->getMockBuilder(Comment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->command = new ApplicationDumpCommand(
            $this->writer,
            [
                [
                    'namespace' => 'system',
                    'source' => $this->source,
                    'pool' => ConfigFilePool::APP_CONFIG,
                    'comment' => $this->commentMock
                ],
                [
                    'namespace' => 'system',
                    'source' => $this->sourceEnv,
                    'pool' => ConfigFilePool::APP_ENV
                ]
            ],
            $this->configHashMock
        );
    }

    public function testExport()
    {
        $dump = [
            'system' => ['systemDATA']
        ];
        $this->configHashMock->expects($this->once())
            ->method('regenerate');
        $this->source
            ->expects($this->once())
            ->method('get')
            ->willReturn(['systemDATA']);
        $this->sourceEnv
            ->expects($this->once())
            ->method('get')
            ->willReturn(['systemDATA']);
        $this->commentMock->expects($this->once())
            ->method('get')
            ->willReturn('Some comment message');
        $this->writer->expects($this->exactly(2))
            ->method('saveConfig')
            ->withConsecutive(
                [[ConfigFilePool::APP_CONFIG => $dump]],
                [[ConfigFilePool::APP_ENV => $dump]]
            );

        $this->output->expects($this->exactly(2))
            ->method('writeln')
            ->withConsecutive(
                [['system' => 'Some comment message']],
                ['<info>Done.</info>']
            );

        $method = new \ReflectionMethod(ApplicationDumpCommand::class, 'execute');
        $method->setAccessible(true);
        $this->assertEquals(
            Cli::RETURN_SUCCESS,
            $method->invokeArgs(
                $this->command,
                [$this->input, $this->output]
            )
        );
    }
}
