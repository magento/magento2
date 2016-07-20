<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobSetCache;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Framework\App\Cache;
use Magento\Framework\Module\PackageInfoFactory;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Setup\Model\Cron\Status as CronStatus;
use Magento\Backend\Console\Command\CacheEnableCommand;
use Magento\Backend\Console\Command\CacheDisableCommand;

/**
 * Class JobSetCacheTest
 */
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
        $objectManagerProvider = $this->getMock(ObjectManagerProvider::class, [], [], '', false);
        $objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class, [], '', false);
        $cleanupFiles = $this->getMock(CleanupFiles::class, [], [], '', false);
        $cache = $this->getMock(Cache::class, [], [], '', false);
        $valueMap = [
            [PackageInfoFactory::class],
            [CleanupFiles::class, $cleanupFiles],
            [Cache::class, $cache],
        ];
        $objectManager->expects($this->atLeastOnce())->method('get')->will($this->returnValueMap($valueMap));
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);

        $output = $this->getMockForAbstractClass(OutputInterface::class, [], '', false);
        $status = $this->getMock(CronStatus::class, [], [], '', false);
        $command = $this->getMock($commandClass, [], [], '', false);

        $command->expects($this->once())
            ->method('run')
            ->with($arrayInput, $output);

        $definition = new InputDefinition([
            new InputArgument('types', InputArgument::REQUIRED),
            new InputArgument('command', InputArgument::REQUIRED),
        ]);

        $inputDef = $this->getMock(InputDefinition::class, [], [], '', false);
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
            [CacheEnableCommand::class, $cacheEnable, 'setup:cache:enable', ['cache1']],
            [CacheDisableCommand::class, $cacheDisable, 'setup:cache:disable', []],
        ];
    }
}
