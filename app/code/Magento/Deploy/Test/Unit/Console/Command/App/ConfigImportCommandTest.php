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

class ConfigImportCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Importer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $importerMock;

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

        $configImportCommand = new ConfigImportCommand(
            $this->importerMock
        );

        $this->commandTester = new CommandTester($configImportCommand);
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $this->importerMock->expects($this->once())
            ->method('import');

        $this->assertSame(Cli::RETURN_SUCCESS, $this->commandTester->execute([]));
    }

    /**
     * @return void
     */
    public function testExecuteWithException()
    {
        $this->importerMock->expects($this->once())
            ->method('import')
            ->willThrowException(new LocalizedException(__('Some error')));

        $this->assertSame(Cli::RETURN_FAILURE, $this->commandTester->execute([]));
        $this->assertContains('Some error', $this->commandTester->getDisplay());
    }
}
