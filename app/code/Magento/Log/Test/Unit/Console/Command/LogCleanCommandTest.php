<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Test\Unit\Console\Command;

use Magento\Log\Console\Command\LogCleanCommand;
use Symfony\Component\Console\Tester\CommandTester;

class LogCleanCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $commandTester = new CommandTester(new LogCleanCommand());
        $commandTester->execute(['--days' => '1']);
        $this->assertSame(
            'Log cleaned.' . PHP_EOL,
            $commandTester->getDisplay()
        );
    }
}
