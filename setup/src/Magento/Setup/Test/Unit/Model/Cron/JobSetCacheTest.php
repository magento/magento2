<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Backend\Console\Command\CacheDisableCommand;
use Magento\Backend\Console\Command\CacheEnableCommand;
use Magento\Framework\App\Cache;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\Cron\JobSetCache;
use Magento\Setup\Model\Cron\Status;
use Magento\Setup\Model\ObjectManagerProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class JobSetCacheTest extends TestCase
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
        $objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $objectManager =
            $this->getMockForAbstractClass(ObjectManagerInterface::class, [], '', false);
        $cleanupFiles = $this->createMock(CleanupFiles::class);
        $cache = $this->createMock(Cache::class);
        $valueMap = [
            [ PackageInfoFactory::class],
            [ CleanupFiles::class, $cleanupFiles],
            [ Cache::class, $cache],
        ];
        $objectManager->expects($this->atLeastOnce())->method('get')->willReturnMap($valueMap);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);

        $output =
            $this->getMockForAbstractClass(OutputInterface::class, [], '', false);
        $status = $this->createMock(Status::class);
        $command = $this->createMock($commandClass);

        $command->expects($this->once())
            ->method('run')
            ->with($arrayInput, $output);

        $definition = new InputDefinition([
            new InputArgument('types', InputArgument::REQUIRED),
            new InputArgument('command', InputArgument::REQUIRED),
        ]);

        $inputDef = $this->createMock(InputDefinition::class);
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
                CacheEnableCommand::class,
                ['command' => 'cache:enable', 'types' => ['cache1']],
                'setup:cache:enable',
                ['cache1']
            ],
            [
                CacheDisableCommand::class,
                ['command' => 'cache:disable'],
                'setup:cache:disable',
                []
            ],
        ];
    }
}
