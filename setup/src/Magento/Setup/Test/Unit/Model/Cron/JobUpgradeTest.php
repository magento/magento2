<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobUpgrade;

class JobUpgradeTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $queue = $this->getMock('Magento\Setup\Model\Cron\Queue', [], [], '', false);
        $queue->expects($this->exactly(3))->method('addJobs');
        $command = $this->getMock('Magento\Setup\Console\Command\UpgradeCommand', [], [], '', false);
        $command->expects($this->once())->method('run');
        $status = $this->getMock('Magento\Setup\Model\Cron\Status', [], [], '', false);
        $output = $this->getMockForAbstractClass('Symfony\Component\Console\Output\OutputInterface', [], '', false);
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface', [], '', false);
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', ['get'], [], '', false);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);

        $cleanupFiles = $this->getMock('\Magento\Framework\App\State\CleanupFiles', [], [], '', false);
        $cache = $this->getMock('\Magento\Framework\App\Cache', [], [], '', false);

        $pathToCacheStatus = '/path/to/cachefile';
        $writeFactory = $this->getMock('\Magento\Framework\Filesystem\Directory\WriteFactory', [], [], '', false);
        $write = $this->getMock('\Magento\Framework\Filesystem\Directory\Write', [], [], '', false);
        $write->expects($this->once())->method('isExist')->with('/path/to/cachefile')->willReturn(true);
        $write->expects($this->once())->method('readFile')->with('/path/to/cachefile')->willReturn(
            '{"cacheOne":1,"cacheTwo":1,"cacheThree":1}'
        );
        $write->expects($this->once())->method('delete')->with('/path/to/cachefile')->willReturn(true);
        $write->expects($this->once())->method('getRelativePath')->willReturn($pathToCacheStatus);

        $writeFactory->expects($this->once())->method('create')->willReturn($write);
        $directoryList = $this->getMock('\Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $directoryList->expects($this->once())->method('getPath')->willReturn('/some/full/path' . $pathToCacheStatus);

        $objectManager->expects($this->any())->method('get')->will($this->returnValueMap([
            ['\Magento\Framework\Filesystem\Directory\WriteFactory', $writeFactory],
            ['\Magento\Framework\App\Filesystem\DirectoryList', $directoryList],
            ['\Magento\Framework\App\State\CleanupFiles', $cleanupFiles],
            ['\Magento\Framework\App\Cache', $cache],
        ]));

        $jobUpgrade = new JobUpgrade(
            $command,
            $objectManagerProvider,
            $output,
            $queue,
            $status,
            'setup:upgrade',
            []
        );
        $jobUpgrade->execute();
    }
}
