<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobSetCache;
use Symfony\Component\Console\Input\ArrayInput;

class JobSetCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider setCacheDataProvider
     * @param string $commandClass
     * @param string $commandName
     * @param string $jobName
     */
    public function testSetCache($commandClass, $commandName, $jobName)
    {
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface', [], '', false);
        $cleanupFiles = $this->getMock('Magento\Framework\App\State\CleanupFiles', [], [], '', false);
        $cache = $this->getMock('Magento\Framework\App\Cache', [], [], '', false);
        $valueMap = [
            ['Magento\Framework\Module\PackageInfoFactory'],
            ['Magento\Framework\App\State\CleanupFiles', $cleanupFiles],
            ['Magento\Framework\App\Cache', $cache],
        ];
        $objectManager->expects($this->atLeastOnce())->method('get')->will($this->returnValueMap($valueMap));
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $output = $this->getMockForAbstractClass('Symfony\Component\Console\Output\OutputInterface', [], '', false);
        $status = $this->getMock('Magento\Setup\Model\Cron\Status', [], [], '', false);
        $command = $this->getMock($commandClass, [], [], '', false);
        $command->expects($this->once())->method('getName')->willReturn($commandName);
        $command->expects($this->once())
            ->method('run')
            ->with(new ArrayInput(['command' => $commandName]), $output);

        $model = new JobSetCache($command, $objectManagerProvider, $output, $status, $jobName, []);
        $model->execute();
    }

    /**
     * @return array
     */
    public function setCacheDataProvider()
    {
        return [
            ['Magento\Backend\Console\Command\CacheEnableCommand', 'cache:enable', 'setup:cache:enable'],
            ['Magento\Backend\Console\Command\CacheDisableCommand', 'cache:disable', 'setup:cache:disable'],
        ];
    }
}
