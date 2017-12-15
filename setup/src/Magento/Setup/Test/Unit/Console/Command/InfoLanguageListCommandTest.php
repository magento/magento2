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

        /** @var \Symfony\Component\Console\Helper\HelperSet|\PHPUnit_Framework_MockObject_MockObject $helperSet */
        $helperSet = $this->createMock(\Symfony\Component\Console\Helper\HelperSet::class);
        $helperSet->expects($this->once())->method('get')->with('table')->will($this->returnValue($table));

        /** @var \Magento\Framework\Setup\Lists|\PHPUnit_Framework_MockObject_MockObject $list */
        $list = $this->createMock(\Magento\Framework\Setup\Lists::class);
        $list->expects($this->once())->method('getLocaleList')->will($this->returnValue($languages));
        $command = new InfoLanguageListCommand($list);
        $command->setHelperSet($helperSet);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
    }
}
