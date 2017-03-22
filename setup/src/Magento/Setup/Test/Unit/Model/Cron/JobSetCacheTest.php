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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class JobSetCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider setCacheDataProvider
     * @param string $commandClass
     * @param array $arrayInput
     * @param string $jobName
     * @param array $params
     */
    public function testSetCache($commandClass, $arrayInput, $jobName, $params)
    {
        $arrayInput = new ArrayInput($arrayInput);
        $objectManagerProvider = $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, [], [], '', false);
        $objectManager =
            $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class, [], '', false);
        $cleanupFiles = $this->getMock(\Magento\Framework\App\State\CleanupFiles::class, [], [], '', false);
        $cache = $this->getMock(\Magento\Framework\App\Cache::class, [], [], '', false);
        $valueMap = [
            [ \Magento\Framework\Module\PackageInfoFactory::class],
            [ \Magento\Framework\App\State\CleanupFiles::class, $cleanupFiles],
            [ \Magento\Framework\App\Cache::class, $cache],
        ];
        $objectManager->expects($this->atLeastOnce())->method('get')->will($this->returnValueMap($valueMap));
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);

        $output =
            $this->getMockForAbstractClass(\Symfony\Component\Console\Output\OutputInterface::class, [], '', false);
        $status = $this->getMock(\Magento\Setup\Model\Cron\Status::class, [], [], '', false);
        $command = $this->getMock($commandClass, [], [], '', false);

        $command->expects($this->once())
            ->method('run')
            ->with($arrayInput, $output);

        $definition = new InputDefinition([
            new InputArgument('types', InputArgument::REQUIRED),
            new InputArgument('command', InputArgument::REQUIRED),
        ]);

        $inputDef = $this->getMock(\Symfony\Component\Console\Input\InputDefinition::class, [], [], '', false);
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
        return [
            [
                \Magento\Backend\Console\Command\CacheEnableCommand::class,
                ['command' => 'cache:enable', 'types' => ['cache1']],
                'setup:cache:enable',
                ['cache1']
            ],
            [
                \Magento\Backend\Console\Command\CacheDisableCommand::class,
                ['command' => 'cache:disable'],
                'setup:cache:disable',
                []
            ],
        ];
    }
}
