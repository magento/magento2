<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console;

use Magento\Setup\Console\CommandList;

class CommandListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Console\CommandList
     */
    private $commandList;

     /**
      * @var \PHPUnit_Framework_MockObject_MockObject|\Zend\ServiceManager\ServiceManager
      */
    private $serviceManager;

    public function setUp()
    {
        $this->serviceManager = $this->getMock('\Zend\ServiceManager\ServiceManager', [], [], '', false);
        $this->commandList = new CommandList($this->serviceManager);
    }

    public function testGetCommands()
    {
        $this->serviceManager->expects($this->atLeastOnce())
            ->method('create');

        $this->commandList->getCommands();
    }
}
