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
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Setup\Console\CommandList
     */
    private $commandList;

     /**
      * @var \PHPUnit\Framework\MockObject\MockObject|\Zend\ServiceManager\ServiceManager
      */
    private $serviceManager;

    protected function setUp(): void
    {
        $this->serviceManager = $this->createMock(\Zend\ServiceManager\ServiceManager::class);
        $this->commandList = new CommandList($this->serviceManager);
    }

    public function testGetCommands()
    {
        $this->serviceManager->expects($this->atLeastOnce())
            ->method('get');

        $this->commandList->getCommands();
    }
}
