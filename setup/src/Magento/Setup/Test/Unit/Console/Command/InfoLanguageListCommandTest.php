<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\InfoLanguageListCommand;
use Symfony\Component\Console\Tester\CommandTester;

class InfoLanguageListCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testExecute()
    {
        $languages = [
            'LNG' => 'Language description'
        ];

        $table = $this->createMock(\Symfony\Component\Console\Helper\Table::class);
        $table->expects($this->once())->method('setHeaders')->with(['Language', 'Code']);
        $table->expects($this->once())->method('addRow')->with(['Language description', 'LNG']);

        /** @var \Symfony\Component\Console\Helper\TableFactory|\PHPUnit\Framework\MockObject\MockObject $helperSet */
        $tableFactoryMock = $this->createMock(\Symfony\Component\Console\Helper\TableFactory::class);
        $tableFactoryMock->expects($this->once())->method('create')->willReturn($table);

        /** @var \Magento\Framework\Setup\Lists|\PHPUnit\Framework\MockObject\MockObject $list */
        $list = $this->createMock(\Magento\Framework\Setup\Lists::class);
        $list->expects($this->once())->method('getLocaleList')->willReturn($languages);
        $command = new InfoLanguageListCommand($list, $tableFactoryMock);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
    }
}
