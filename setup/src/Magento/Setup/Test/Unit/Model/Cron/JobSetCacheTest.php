<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
class JobSetCacheTest extends \PHPUnit\Framework\TestCase
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
        $objectManagerProvider = $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);
        $objectManager =
            $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class, [], '', false);
        $cleanupFiles = $this->createMock(\Magento\Framework\App\State\CleanupFiles::class);
        $cache = $this->createMock(\Magento\Framework\App\Cache::class);
        $valueMap = [
            [ \Magento\Framework\Module\PackageInfoFactory::class],
            [ \Magento\Framework\App\State\CleanupFiles::class, $cleanupFiles],
            [ \Magento\Framework\App\Cache::class, $cache],
        ];
        $objectManager->expects($this->atLeastOnce())->method('get')->will($this->returnValueMap($valueMap));
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);

        $output =
            $this->getMockForAbstractClass(\Symfony\Component\Console\Output\OutputInterface::class, [], '', false);
        $status = $this->createMock(\Magento\Setup\Model\Cron\Status::class);
        $command = $this->createMock($commandClass);

        $command->expects($this->once())
            ->method('run')
            ->with($arrayInput, $output);

        $definition = new InputDefinition([
            new InputArgument('types', InputArgument::REQUIRED),
            new InputArgument('command', InputArgument::REQUIRED),
        ]);

        $inputDef = $this->createMock(\Symfony\Component\Console\Input\InputDefinition::class);
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
