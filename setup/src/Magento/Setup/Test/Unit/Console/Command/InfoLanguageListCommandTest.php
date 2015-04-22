<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\InfoLanguageListCommand;
use Symfony\Component\Console\Tester\CommandTester;

class InfoLanguageListCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $languages = [
            'LNG' => 'Language description'
        ];
        /** @var \Magento\Setup\Model\Lists|\PHPUnit_Framework_MockObject_MockObject $list */
        $list = $this->getMock('Magento\Setup\Model\Lists', [], [], '', false);
        $list->expects($this->once())->method('getLocaleList')->will($this->returnValue($languages));
        $commandTester = new CommandTester(new InfoLanguageListCommand($list));
        $commandTester->execute([]);
        $this->assertStringMatchesFormat(
            'LNG => Language description',
            $commandTester->getDisplay()
        );
    }
}
