<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobDbRollback;

class JobDbRollbackTest extends \PHPUnit_Framework_TestCase
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
        $this->backupRollbackFactory = $this->getMock(
            \Magento\Framework\Setup\BackupRollbackFactory::class,
            [],
            [],
            '',
            false
        );
        $this->backupRollback = $this->getMock(\Magento\Framework\Setup\BackupRollback::class, [], [], '', false);
        $this->status = $this->getMock(\Magento\Setup\Model\Cron\Status::class, [], [], '', false);
        $output =
            $this->getMockForAbstractClass(\Symfony\Component\Console\Output\OutputInterface::class, [], '', false);
        $this->objectManagerProvider =
            $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, [], [], '', false);

        $appState = $this->getMock(
            \Magento\Framework\App\State::class,
            [],
            [],
            '',
            false
        );
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
