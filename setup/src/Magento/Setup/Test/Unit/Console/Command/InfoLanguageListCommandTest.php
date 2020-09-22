<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\Setup\Lists;
use Magento\Setup\Console\Command\InfoLanguageListCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Tester\CommandTester;

class InfoLanguageListCommandTest extends TestCase
{
    public function testExecute()
    {
        $languages = [
            'LNG' => 'Language description'
        ];

        $table = $this->createMock(Table::class);
        $table->expects($this->once())->method('setHeaders')->with(['Language', 'Code']);
        $table->expects($this->once())->method('addRow')->with(['Language description', 'LNG']);

        /** @var \Symfony\Component\Console\Helper\TableFactory|MockObject $helperSet */
        $tableFactoryMock = $this->createMock(\Symfony\Component\Console\Helper\TableFactory::class);
        $tableFactoryMock->expects($this->once())->method('create')->willReturn($table);

        /** @var Lists|MockObject $list */
        $list = $this->createMock(Lists::class);
        $list->expects($this->once())->method('getLocaleList')->willReturn($languages);
        $command = new InfoLanguageListCommand($list, $tableFactoryMock);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
    }
}
