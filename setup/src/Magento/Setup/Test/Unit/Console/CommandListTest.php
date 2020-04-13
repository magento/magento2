<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console;

use Magento\Setup\Console\CommandList;

class CommandListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Console\CommandList
     */
    private $commandList;

     /**
      * @var \PHPUnit_Framework_MockObject_MockObject|\Laminas\ServiceManager\ServiceManager
      */
    private $serviceManager;

    public function setUp()
    {
        $this->serviceManager = $this->createMock(\Laminas\ServiceManager\ServiceManager::class);
        $this->commandList = new CommandList($this->serviceManager);
    }

    public function testGetCommands()
    {
        $this->serviceManager->expects($this->atLeastOnce())
            ->method('get');

        $this->commandList->getCommands();
    }
}
