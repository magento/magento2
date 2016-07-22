<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Backend\Console\Command\CacheDisableCommand;
use Magento\Backend\Console\Command\CacheEnableCommand;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Console\Command\ModuleDisableCommand;
use Magento\Setup\Console\Command\ModuleEnableCommand;
use Magento\Setup\Console\Command\UpgradeCommand;
use Symfony\Component\Console\Command\Command;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory class to create jobs
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class JobFactory
{
    /**
     * Name of jobs
     */
    const JOB_UPGRADE = 'setup:upgrade';
    const JOB_DB_ROLLBACK = 'setup:rollback';
    const JOB_COMPONENT_UNINSTALL = 'setup:component:uninstall';
    const JOB_MODULE_ENABLE = 'setup:module:enable';
    const JOB_MODULE_DISABLE = 'setup:module:disable';
    const JOB_STATIC_REGENERATE = 'setup:static:regenerate';
    const JOB_ENABLE_CACHE = 'setup:cache:enable';
    const JOB_DISABLE_CACHE = 'setup:cache:disable';

    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Create job instance.
     *
     * @param string $name
     * @param array $params
     * @return AbstractJob
     * @throws \RuntimeException
     */
    public function create($name, array $params = [])
    {
        $cronStatus = $this->serviceLocator->get('Magento\Setup\Model\Cron\Status');
        $statusStream = fopen($cronStatus->getStatusFilePath(), 'a+');
        $logStream = fopen($cronStatus->getLogFilePath(), 'a+');
        $streamOutput = new MultipleStreamOutput([$statusStream, $logStream]);
        $objectManagerProvider = $this->serviceLocator->get('Magento\Setup\Model\ObjectManagerProvider');
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = $objectManagerProvider->get();
        switch ($name) {
            case self::JOB_UPGRADE:
                $cmd = $this->serviceLocator->get(UpgradeCommand::class);
                $this->prepareCommand($objectManager, $cmd);
                return new JobUpgrade(
                    $cmd,
                    $objectManagerProvider,
                    $streamOutput,
                    $this->serviceLocator->get('Magento\Setup\Model\Cron\Queue'),
                    $cronStatus,
                    $name,
                    $params
                );
                break;
            case self::JOB_DB_ROLLBACK:
                return new JobDbRollback(
                    $objectManager->get('Magento\Framework\Setup\BackupRollbackFactory'),
                    $streamOutput,
                    $cronStatus,
                    $objectManagerProvider,
                    $name,
                    $params
                );
                break;
            case self::JOB_STATIC_REGENERATE:
                return new JobStaticRegenerate(
                    $objectManagerProvider,
                    $streamOutput,
                    $cronStatus,
                    $name,
                    $params
                );
                break;
            case self::JOB_COMPONENT_UNINSTALL:
                $moduleUninstall = new Helper\ModuleUninstall(
                    $this->serviceLocator->get('Magento\Setup\Model\ModuleUninstaller'),
                    $this->serviceLocator->get('Magento\Setup\Model\ModuleRegistryUninstaller'),
                    $objectManager->get('Magento\Framework\Module\PackageInfoFactory')
                );
                $themeUninstall = new Helper\ThemeUninstall(
                    $objectManager->get('Magento\Theme\Model\Theme\ThemeUninstaller'),
                    $objectManager->get('Magento\Theme\Model\Theme\ThemePackageInfo')
                );
                return new JobComponentUninstall(
                    $objectManager->get('Magento\Framework\Composer\ComposerInformation'),
                    $moduleUninstall,
                    $themeUninstall,
                    $objectManagerProvider,
                    $streamOutput,
                    $this->serviceLocator->get('Magento\Setup\Model\Cron\Queue'),
                    $cronStatus,
                    $this->serviceLocator->get('Magento\Setup\Model\Updater'),
                    $name,
                    $params
                );
                break;
            case self::JOB_MODULE_ENABLE:
                $cmd = $this->serviceLocator->get(ModuleEnableCommand::class);
                $this->prepareCommand($objectManager, $cmd);
                return new JobModule(
                    $cmd,
                    $objectManagerProvider,
                    $streamOutput,
                    $cronStatus,
                    $name,
                    $params
                );
                break;
            case self::JOB_MODULE_DISABLE:
                $cmd = $this->serviceLocator->get(ModuleDisableCommand::class);
                $this->prepareCommand($objectManager, $cmd);
                return new JobModule(
                    $cmd,
                    $objectManagerProvider,
                    $streamOutput,
                    $cronStatus,
                    $name,
                    $params
                );
                break;
            case self::JOB_ENABLE_CACHE:
                $cmd = $objectManager->get(CacheEnableCommand::class);
                $this->prepareCommand($objectManager, $cmd);
                return new JobSetCache(
                    $cmd,
                    $objectManagerProvider,
                    $streamOutput,
                    $cronStatus,
                    $name,
                    $params
                );
                break;
            case self::JOB_DISABLE_CACHE:
                $cmd = $objectManager->get(CacheDisableCommand::class);
                $this->prepareCommand($objectManager, $cmd);
                return new JobSetCache(
                    $cmd,
                    $objectManagerProvider,
                    $streamOutput,
                    $cronStatus,
                    $name
                );
                break;
            default:
                throw new \RuntimeException(sprintf('"%s" job is not supported.', $name));
                break;
        }
    }

    /**
     * Prepare command, set application if needed
     *
     * @param ObjectManagerInterface $objectManager
     * @param Command $command
     */
    private function prepareCommand(
        ObjectManagerInterface $objectManager,
        Command $command
    ) {
        if (null === $command->getApplication()) {
            $command->setApplication(
                $objectManager->get(\Magento\Framework\Console\Cli::class)
            );
        }
    }
}
