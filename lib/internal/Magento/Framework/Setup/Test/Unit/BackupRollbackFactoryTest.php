<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit;

use Magento\Framework\Setup\BackupRollbackFactory;

class BackupRollbackFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $objectManager = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $consoleLogger = $this->createMock(\Magento\Framework\Setup\ConsoleLogger::class);
        $factory = $this->createMock(\Magento\Framework\Setup\BackupRollback::class);
        $output = $this->getMockForAbstractClass(
            \Symfony\Component\Console\Output\OutputInterface::class,
            [],
            '',
            false
        );
        $objectManager->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap([
                [\Magento\Framework\Setup\ConsoleLogger::class, ['output' => $output], $consoleLogger],
                [\Magento\Framework\Setup\BackupRollback::class, ['log' => $consoleLogger], $factory],
            ]);
        $model = new BackupRollbackFactory($objectManager);
        $this->assertInstanceOf(\Magento\Framework\Setup\BackupRollback::class, $model->create($output));
    }
}
