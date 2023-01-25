<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\BackupRollback;
use Magento\Framework\Setup\BackupRollbackFactory;
use Magento\Framework\Setup\ConsoleLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class BackupRollbackFactoryTest extends TestCase
{
    public function testCreate()
    {
        $objectManager = $this->getMockForAbstractClass(
            ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $consoleLogger = $this->createMock(ConsoleLogger::class);
        $factory = $this->createMock(BackupRollback::class);
        $output = $this->getMockForAbstractClass(
            OutputInterface::class,
            [],
            '',
            false
        );
        $objectManager->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap([
                [ConsoleLogger::class, ['output' => $output], $consoleLogger],
                [BackupRollback::class, ['log' => $consoleLogger], $factory],
            ]);
        $model = new BackupRollbackFactory($objectManager);
        $this->assertInstanceOf(BackupRollback::class, $model->create($output));
    }
}
