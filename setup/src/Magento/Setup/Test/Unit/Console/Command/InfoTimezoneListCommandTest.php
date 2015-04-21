<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\InfoTimezoneListCommand;
use Symfony\Component\Console\Tester\CommandTester;

class InfoTimezoneListCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $timezones = [
            'timezone' => 'timezone description'
        ];

        /** @var \Magento\Setup\Model\Lists|\PHPUnit_Framework_MockObject_MockObject $list */
        $list = $this->getMock('Magento\Setup\Model\Lists', [], [], '', false);
        $list->expects($this->once())->method('getTimezoneList')->will($this->returnValue($timezones));
        $commandTester = new CommandTester(new InfoTimezoneListCommand($list));
        $commandTester->execute([]);
        $this->assertStringMatchesFormat(
            'timezone => timezone description',
            $commandTester->getDisplay()
        );

    }
}
