<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\InfoBackupsListCommand;
use Symfony\Component\Console\Tester\CommandTester;

class InfoBackupsListCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $table = $this->getMock(\Symfony\Component\Console\Helper\Table::class, [], [], '', false);
        $table->expects($this->once())->method('setHeaders')->with(['Backup Filename', 'Backup Type']);
        $table->expects($this->once())->method('addRow')->with(['backupFile_media.tgz', 'media']);
        /** @var \Symfony\Component\Console\Helper\HelperSet|\PHPUnit_Framework_MockObject_MockObject $helperSet */
        $helperSet = $this->getMock(\Symfony\Component\Console\Helper\HelperSet::class, [], [], '', false);
        $helperSet->expects($this->once())->method('get')->with('table')->will($this->returnValue($table));
        /** @var \Magento\Framework\App\Filesystem\DirectoryList
         * |\PHPUnit_Framework_MockObject_MockObject $directoryList
         */
        $directoryList = $this->getMock(\Magento\Framework\App\Filesystem\DirectoryList::class, [], [], '', false);
        /** @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit_Framework_MockObject_MockObject $file */
        $file = $this->getMock(\Magento\Framework\Filesystem\Driver\File::class, [], [], '', false);
        $file->expects($this->once())->method('isExists')->will($this->returnValue(true));
        $file->expects($this->once())
            ->method('readDirectoryRecursively')
            ->will($this->returnValue(['backupFile_media.tgz']));
        $command = new InfoBackupsListCommand($directoryList, $file);
        $command->setHelperSet($helperSet);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $expected = 'Showing backup files in ';
        $this->assertStringStartsWith($expected, $commandTester->getDisplay());
    }
}
