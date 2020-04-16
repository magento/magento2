<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\InfoBackupsListCommand;
use Symfony\Component\Console\Tester\CommandTester;

class InfoBackupsListCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testExecute()
    {
        $table = $this->createMock(\Symfony\Component\Console\Helper\Table::class);
        $table->expects($this->once())->method('setHeaders')->with(['Backup Filename', 'Backup Type']);
        $table->expects($this->once())->method('addRow')->with(['backupFile_media.tgz', 'media']);
        /** @var \Symfony\Component\Console\Helper\TableFactory|\PHPUnit\Framework\MockObject\MockObject $helperSet */
        $tableFactoryMock = $this->createMock(\Symfony\Component\Console\Helper\TableFactory::class);
        $tableFactoryMock->expects($this->once())->method('create')->willReturn($table);
        /** @var \Magento\Framework\App\Filesystem\DirectoryList
         * |\PHPUnit\Framework\MockObject\MockObject $directoryList
         */
        $directoryList = $this->createMock(\Magento\Framework\App\Filesystem\DirectoryList::class);
        /** @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit\Framework\MockObject\MockObject $file */
        $file = $this->createMock(\Magento\Framework\Filesystem\Driver\File::class);
        $file->expects($this->once())->method('isExists')->willReturn(true);
        $file->expects($this->once())
            ->method('readDirectoryRecursively')
            ->willReturn(['backupFile_media.tgz']);
        $command = new InfoBackupsListCommand($directoryList, $file, $tableFactoryMock);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $expected = 'Showing backup files in ';
        $this->assertStringStartsWith($expected, $commandTester->getDisplay());
    }
}
