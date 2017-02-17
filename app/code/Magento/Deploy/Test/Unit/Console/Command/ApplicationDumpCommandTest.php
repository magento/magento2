<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Console\Command;

use Magento\Deploy\Console\Command\App\ApplicationDumpCommand;
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
     * @var ApplicationDumpCommand
     */
    private $command;

    public function setUp()
    {
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

        $this->command = new ApplicationDumpCommand($this->writer, [[
            'namespace' => 'system',
            'source' => $this->source
        ]]);
    }

    public function testExport()
    {
        $dump = [
            'system' => ['systemDATA']
        ];
        $data = [ConfigFilePool::APP_CONFIG => $dump];
        $this->source
            ->expects($this->once())
            ->method('get')
            ->willReturn(['systemDATA']);
        $this->output->expects($this->once())
            ->method('writeln')
            ->with('<info>Done.</info>');
        $this->writer->expects($this->once())
            ->method('saveConfig')
            ->with($data);
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
