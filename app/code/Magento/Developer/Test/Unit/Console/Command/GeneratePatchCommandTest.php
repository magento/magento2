<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Developer\Console\Command\GeneratePatchCommand;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Symfony\Component\Console\Tester\CommandTester;

class GeneratePatchCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @var WriteFactory
     */
    private $writeFactory;

    /**
     * @var GeneratePatchCommand
     */
    private $command;

    protected function setUp()
    {
        $this->componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $this->directoryList = $this->createMock(DirectoryList::class);
        $this->readFactory = $this->createMock(ReadFactory::class);
        $this->writeFactory = $this->createMock(WriteFactory::class);

        $this->command = new GeneratePatchCommand(
            $this->componentRegistrar,
            $this->directoryList,
            $this->readFactory,
            $this->writeFactory
        );
    }

    public function testExecute()
    {
        $this->componentRegistrar->expects($this->once())
            ->method('getPath')
            ->with('module', 'Vendor_Module')
            ->willReturn('/long/path/to/Vendor/Module');

        $read = $this->createMock(\Magento\Framework\Filesystem\Directory\Read::class);
        $read->expects($this->at(0))
            ->method('readFile')
            ->with('patch_template.php.dist')
            ->willReturn('something');
        $this->readFactory->method('create')->willReturn($read);

        $write = $this->createMock(\Magento\Framework\Filesystem\Directory\Write::class);
        $write->expects($this->once())->method('writeFile');
        $this->writeFactory->method('create')->willReturn($write);

        $this->directoryList->expects($this->once())->method('getRoot')->willReturn('/some/path');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(
            [
                GeneratePatchCommand::MODULE_NAME => 'Vendor_Module',
                GeneratePatchCommand::INPUT_KEY_PATCH_NAME => 'SomePatch'
            ]
        );
        $this->assertContains('successfully generated', $commandTester->getDisplay());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not enough arguments
     */
    public function testWrongParameter()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot find a registered module with name "Fake_Module"
     */
    public function testBadModule()
    {
        $this->componentRegistrar->expects($this->once())
            ->method('getPath')
            ->with('module', 'Fake_Module')
            ->willReturn(null);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(
            [
                GeneratePatchCommand::MODULE_NAME => 'Fake_Module',
                GeneratePatchCommand::INPUT_KEY_PATCH_NAME => 'SomePatch'
            ]
        );
        $this->assertContains('successfully generated', $commandTester->getDisplay());
    }
}
