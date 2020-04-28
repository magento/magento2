<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Setup\Console\Command\InfoBackupsListCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Tester\CommandTester;

class InfoBackupsListCommandTest extends TestCase
{
    public function testExecute()
    {
        $table = $this->createMock(Table::class);
        $table->expects($this->once())->method('setHeaders')->with(['Backup Filename', 'Backup Type']);
        $table->expects($this->once())->method('addRow')->with(['backupFile_media.tgz', 'media']);
        /** @var \Symfony\Component\Console\Helper\TableFactory|MockObject $helperSet */
        $tableFactoryMock = $this->createMock(\Symfony\Component\Console\Helper\TableFactory::class);
        $tableFactoryMock->expects($this->once())->method('create')->will($this->returnValue($table));
        /** @var DirectoryList
         * |\PHPUnit_Framework_MockObject_MockObject $directoryList
         */
        $directoryList = $this->createMock(DirectoryList::class);
        /** @var File|MockObject $file */
        $file = $this->createMock(File::class);
        $file->expects($this->once())->method('isExists')->will($this->returnValue(true));
        $file->expects($this->once())
            ->method('readDirectoryRecursively')
            ->will($this->returnValue(['backupFile_media.tgz']));
        $command = new InfoBackupsListCommand($directoryList, $file, $tableFactoryMock);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $expected = 'Showing backup files in ';
        $this->assertStringStartsWith($expected, $commandTester->getDisplay());
    }
}
