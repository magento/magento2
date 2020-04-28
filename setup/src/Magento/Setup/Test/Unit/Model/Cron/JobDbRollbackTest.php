<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Framework\App\State;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\BackupRollback;
use Magento\Framework\Setup\BackupRollbackFactory;
use Magento\Setup\Model\Cron\JobDbRollback;
use Magento\Setup\Model\Cron\Status;
use Magento\Setup\Model\ObjectManagerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class JobDbRollbackTest extends TestCase
{
    /**
     * @var JobDbRollback
     */
    private $jobDbRollback;

    /**
     * @var MockObject|BackupRollbackFactory
     */
    private $backupRollbackFactory;

    /**
     * @var MockObject|BackupRollback
     */
    private $backupRollback;

    /**
     * @var MockObject|Status
     */
    private $status;

    /**
     * @var MockObject|ObjectManagerProvider
     */
    private $objectManagerProvider;

    protected function setup(): void
    {
        $this->backupRollbackFactory = $this->createMock(BackupRollbackFactory::class);
        $this->backupRollback = $this->createMock(BackupRollback::class);
        $this->status = $this->createMock(Status::class);
        $output =
            $this->getMockForAbstractClass(OutputInterface::class, [], '', false);
        $this->objectManagerProvider =
            $this->createMock(ObjectManagerProvider::class);

        $appState = $this->createMock(State::class);
        $configLoader = $this->getMockForAbstractClass(
            ConfigLoaderInterface::class,
            [],
            '',
            false
        );
        $configLoader->expects($this->any())->method('load')->willReturn([]);
        $objectManager =
            $this->getMockForAbstractClass(ObjectManagerInterface::class, [], '', false);
        $objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [State::class, $appState],
                [ConfigLoaderInterface::class, $configLoader],
            ]);

        $this->objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);

        $this->jobDbRollback = new JobDbRollback(
            $this->backupRollbackFactory,
            $output,
            $this->status,
            $this->objectManagerProvider,
            'setup:rollback',
            ['backup_file_name' => 'someFileName']
        );
    }

    public function testExecute()
    {
        $this->backupRollbackFactory->expects($this->once())->method('create')->willReturn($this->backupRollback);
        $this->backupRollback->expects($this->once())->method('dbRollback');
        $this->jobDbRollback->execute();
    }

    public function testExceptionOnExecute()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Could not complete');
        $this->backupRollbackFactory->expects($this->once())->method('create')->willThrowException(new \Exception());
        $this->jobDbRollback->execute();
    }
}
