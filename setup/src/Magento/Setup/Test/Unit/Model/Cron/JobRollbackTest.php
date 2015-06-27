<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobRollback;

class JobRollbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobRollback
     */
    private $jobRollback;

    public function setup()
    {
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface', [], '', false);
        $backupRollbackFactory = $this->getMock('Magento\Framework\Setup\BackupRollbackFactory', [], [], '', false);
        $backupRollback = $this->getMock('\Magento\Framework\Setup\BackupRollback', [], [], '', false);
        $backupRollbackFactory->expects($this->once())->method('create')->willReturn($backupRollback);
        $backupRollback->expects($this->once())->method('dbRollback');
        $dirList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $dirList->expects($this->once())->method('getPath')->willReturn('some/path');
        $valueMap = [
            ['Magento\Framework\App\Filesystem\DirectoryList', $dirList],
            ['Magento\Framework\Setup\BackupRollbackFactory', $backupRollbackFactory],
        ];
        $objectManager->expects($this->atLeastOnce())->method('get')->will($this->returnValueMap($valueMap));
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);

        $status = $this->getMock('Magento\Setup\Model\Cron\Status', [], [], '', false);
        $maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $maintenanceMode->expects($this->once())->method('set')->with(false);
        $output = $this->getMockForAbstractClass('Symfony\Component\Console\Output\OutputInterface', [], '', false);
        $this->jobRollback = new JobRollback(
            $objectManagerProvider,
            $maintenanceMode,
            $output,
            $status,
            'setup:rollback',
            []
        );
    }

    public function testExecute()
    {
        $this->jobRollback->execute();
    }
}

// functions to override native php functions
namespace Magento\Setup\Model\Cron;

function scandir($inputDir)
{
    if ($inputDir == 'some/path/backups') {
        return ['file1_code', 'file2_db'];
    } else {
        return [];
    }

}
