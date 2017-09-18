<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobDbRollback;

class JobDbRollbackTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var JobDbRollback
     */
    private $jobDbRollback;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Setup\BackupRollbackFactory
     */
    private $backupRollbackFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Setup\BackupRollback
     */
    private $backupRollback;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Cron\Status
     */
    private $status;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ObjectManagerProvider
     */
    private $objectManagerProvider;

    public function setup()
    {
        $this->backupRollbackFactory = $this->createMock(\Magento\Framework\Setup\BackupRollbackFactory::class);
        $this->backupRollback = $this->createMock(\Magento\Framework\Setup\BackupRollback::class);
        $this->status = $this->createMock(\Magento\Setup\Model\Cron\Status::class);
        $output =
            $this->getMockForAbstractClass(\Symfony\Component\Console\Output\OutputInterface::class, [], '', false);
        $this->objectManagerProvider =
            $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);

        $appState = $this->createMock(\Magento\Framework\App\State::class);
        $configLoader = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManager\ConfigLoaderInterface::class,
            [],
            '',
            false
        );
        $configLoader->expects($this->any())->method('load')->willReturn([]);
        $objectManager =
            $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class, [], '', false);
        $objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap([
                [\Magento\Framework\App\State::class, $appState],
                [\Magento\Framework\ObjectManager\ConfigLoaderInterface::class, $configLoader],
            ]));

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

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not complete
     */
    public function testExceptionOnExecute()
    {
        $this->backupRollbackFactory->expects($this->once())->method('create')->willThrowException(new \Exception);
        $this->jobDbRollback->execute();
    }
}
