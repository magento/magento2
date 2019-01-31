<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\InfoTimezoneListCommand;
use Symfony\Component\Console\Tester\CommandTester;

class InfoTimezoneListCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testExecute()
    {
        $timezones = [
            'timezone' => 'timezone description'
        ];

        $table = $this->createMock(\Symfony\Component\Console\Helper\Table::class);
        $table->expects($this->once())->method('setHeaders')->with(['Timezone', 'Code']);
        $table->expects($this->once())->method('addRow')->with(['timezone description', 'timezone']);

        /** @var \Symfony\Component\Console\Helper\TableFactory|\PHPUnit_Framework_MockObject_MockObject $helperSet */
        $tableFactoryMock = $this->createMock(\Symfony\Component\Console\Helper\TableFactory::class);
        $tableFactoryMock->expects($this->once())->method('create')->will($this->returnValue($table));

        /** @var \Magento\Framework\Setup\Lists|\PHPUnit_Framework_MockObject_MockObject $list */
        $list = $this->createMock(\Magento\Framework\Setup\Lists::class);
        $list->expects($this->once())->method('getTimezoneList')->will($this->returnValue($timezones));
        $command = new InfoTimezoneListCommand($list, $tableFactoryMock);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
    }
}
