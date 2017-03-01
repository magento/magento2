<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Console\Command\App;

use Magento\Deploy\Console\Command\App\ConfigImportCommand;
use Magento\Deploy\Console\Command\App\ConfigImport\Importer;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\App\DeploymentConfig\Writer;

class ConfigImportCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Importer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $importerMock;

    /**
     * @var Writer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $writerMock;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->importerMock = $this->getMockBuilder(Importer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->writerMock = $this->getMockBuilder(Writer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configImportCommand = new ConfigImportCommand(
            $this->importerMock,
            $this->writerMock
        );

        $this->commandTester = new CommandTester($configImportCommand);
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $this->writerMock->expects($this->once())
            ->method('checkIfWritable')
            ->willReturn(true);
        $this->importerMock->expects($this->once())
            ->method('import');

        $this->assertSame(Cli::RETURN_SUCCESS, $this->commandTester->execute([]));
    }

    /**
     * @return void
     */
    public function testExecuteWithoutWritePermissions()
    {
        $this->writerMock->expects($this->once())
            ->method('checkIfWritable')
            ->willReturn(false);
        $this->importerMock->expects($this->never())
            ->method('import');

        $this->assertSame(Cli::RETURN_FAILURE, $this->commandTester->execute([]));
        $this->assertContains('Deployment configuration file is not writable.', $this->commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testExecuteWithException()
    {
        $this->writerMock->expects($this->once())
            ->method('checkIfWritable')
            ->willReturn(true);
        $this->importerMock->expects($this->once())
            ->method('import')
            ->willThrowException(new LocalizedException(__('Some error')));

        $this->assertSame(Cli::RETURN_FAILURE, $this->commandTester->execute([]));
        $this->assertContains('Some error', $this->commandTester->getDisplay());
    }
}
