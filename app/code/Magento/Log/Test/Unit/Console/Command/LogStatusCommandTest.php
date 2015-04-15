<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Test\Unit\Console\Command;

use Magento\Log\Console\Command\LogStatusCommand;
use Symfony\Component\Console\Tester\CommandTester;

class LogStatusCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $commandTester = new CommandTester(new LogStatusCommand());
        $commandTester->execute([]);
        $this->assertStringStartsWith(
            '-----------------------------------+------------+------------+------------+' . PHP_EOL
            . 'Table Name                         | Rows       | Data Size  | Index Size |'  . PHP_EOL
            . '-----------------------------------+------------+------------+------------+' . PHP_EOL,
            $commandTester->getDisplay()
        );
    }
}
