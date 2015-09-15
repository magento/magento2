<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Console;

use \Magento\Framework\Console\CommandLocator;

class CommandLocatorTest extends \PHPUnit_Framework_TestCase
{
    public function testLocator()
    {
        CommandLocator::register('\CommandListClass1');
        CommandLocator::register('\CommandListClass2');
        $this->assertEquals(['\CommandListClass1', '\CommandListClass2'], CommandLocator::getCommands());
    }
}
