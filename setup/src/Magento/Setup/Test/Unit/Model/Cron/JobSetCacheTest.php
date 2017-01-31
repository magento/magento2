<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobSetCache;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;

class JobSetCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider setCacheDataProvider
     * @param string $commandClass
     * @param string $arrayInput
     * @param string $jobName
     * @param array $params
     */
    public function testSetCache($commandClass, $arrayInput, $jobName, $params)
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

        $command->expects($this->once())
            ->method('run')
            ->with($arrayInput, $output);

        $definition = new InputDefinition([
            new InputArgument('types', InputArgument::REQUIRED),
            new InputArgument('command', InputArgument::REQUIRED),
        ]);

        $inputDef = $this->getMock('\Symfony\Component\Console\Input\InputDefinition', [], [], '', false);
        $inputDef->expects($this->any())->method('hasArgument')->willReturn(true);
        $command->expects($this->any())->method('getDefinition')->willReturn($inputDef);
        $command->expects($this->any())->method('setDefinition')->with($definition);

        $model = new JobSetCache($command, $objectManagerProvider, $output, $status, $jobName, $params);
        $model->execute();
    }

    /**
     * @return array
     */
    public function setCacheDataProvider()
    {
        $cacheEnable = new ArrayInput(['command' => 'cache:enable', 'types' => ['cache1']]);
        $cacheDisable = new ArrayInput(['command' => 'cache:disable']);
        return [
            ['Magento\Backend\Console\Command\CacheEnableCommand', $cacheEnable, 'setup:cache:enable', ['cache1']],
            ['Magento\Backend\Console\Command\CacheDisableCommand', $cacheDisable, 'setup:cache:disable', []],
        ];
    }
}
