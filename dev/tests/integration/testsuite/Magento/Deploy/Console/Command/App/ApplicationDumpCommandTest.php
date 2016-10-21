<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\TestFramework\Helper\Bootstrap;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApplicationDumpCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApplicationDumpCommand
     */
    private $command;

    public function setUp()
    {
        $this->command = Bootstrap::getObjectManager()->get(ApplicationDumpCommand::class);
    }

    public function testExecute()
    {
        $inputMock = $this->getMock(InputInterface::class);
        $outputMock = $this->getMock(OutputInterface::class);
        $outputMock->expects($this->once())
            ->method('writeln')
            ->with('<info>Done.</info>');
        $this->assertEquals(0, $this->command->run($inputMock, $outputMock));
    }
}
