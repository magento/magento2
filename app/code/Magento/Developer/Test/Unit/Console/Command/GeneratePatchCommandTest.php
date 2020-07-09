<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Developer\Console\Command\GeneratePatchCommand;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\DirectoryList;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class GeneratePatchCommandTest extends TestCase
{
    /**
     * @var ComponentRegistrar|MockObject
     */
    private $componentRegistrarMock;

    /**
     * @var DirectoryList|MockObject
     */
    private $directoryListMock;

    /**
     * @var ReadFactory|MockObject
     */
    private $readFactoryMock;

    /**
     * @var WriteFactory|MockObject
     */
    private $writeFactoryMock;

    /**
     * @var GeneratePatchCommand|MockObject
     */
    private $command;

    protected function setUp(): void
    {
        $this->componentRegistrarMock = $this->createMock(ComponentRegistrar::class);
        $this->directoryListMock = $this->createMock(DirectoryList::class);
        $this->readFactoryMock = $this->createMock(ReadFactory::class);
        $this->writeFactoryMock = $this->createMock(WriteFactory::class);

        $this->command = new GeneratePatchCommand(
            $this->componentRegistrarMock,
            $this->directoryListMock,
            $this->readFactoryMock,
            $this->writeFactoryMock
        );
    }

    public function testExecute()
    {
        $this->componentRegistrarMock->expects($this->once())
            ->method('getPath')
            ->with('module', 'Vendor_Module')
            ->willReturn('/long/path/to/Vendor/Module');

        $read = $this->createMock(Read::class);
        $read->expects($this->at(0))
            ->method('readFile')
            ->with('patch_template.php.dist')
            ->willReturn('something');
        $this->readFactoryMock->method('create')->willReturn($read);

        $write = $this->createMock(Write::class);
        $write->expects($this->once())->method('writeFile');
        $this->writeFactoryMock->method('create')->willReturn($write);

        $this->directoryListMock->expects($this->once())->method('getRoot')->willReturn('/some/path');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(
            [
                GeneratePatchCommand::MODULE_NAME => 'Vendor_Module',
                GeneratePatchCommand::INPUT_KEY_PATCH_NAME => 'SomePatch'
            ]
        );
        $this->assertStringContainsString('successfully generated', $commandTester->getDisplay());
    }

    public function testWrongParameter()
    {
        $this->expectExceptionMessage('Not enough arguments');
        $this->expectException(\RuntimeException::class);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
    }

    public function testBadModule()
    {
        $this->componentRegistrarMock->expects($this->once())
            ->method('getPath')
            ->with('module', 'Fake_Module')
            ->willReturn(null);

        $this->expectExceptionMessage('Cannot find a registered module with name "Fake_Module"');
        $this->expectException(\InvalidArgumentException::class);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(
            [
                GeneratePatchCommand::MODULE_NAME => 'Fake_Module',
                GeneratePatchCommand::INPUT_KEY_PATCH_NAME => 'SomePatch'
            ]
        );
    }
}
