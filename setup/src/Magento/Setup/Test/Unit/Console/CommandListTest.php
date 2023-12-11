<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console;

use Laminas\ServiceManager\ServiceManager;
use Magento\Setup\Console\CommandList;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CommandListTest extends TestCase
{
    /**
     * @var MockObject|CommandList
     */
    private $commandList;

    /**
     * @var MockObject|ServiceManager
     */
    private $serviceManager;

    protected function setUp(): void
    {
        $this->serviceManager = $this->createMock(ServiceManager::class);
        $this->commandList = new CommandList($this->serviceManager);
    }

    public function testGetCommands()
    {
        $this->serviceManager->expects($this->atLeastOnce())
            ->method('get');

        $this->commandList->getCommands();
    }
}
