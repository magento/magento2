<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

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
        /** @var \Magento\Framework\ObjectManagerInterface $objectManager */
        $objectManager = $objectManagerProvider->get();
        switch ($name) {
            case self::JOB_UPGRADE:
                return new JobUpgrade(
                    $this->serviceLocator->get('Magento\Setup\Console\Command\UpgradeCommand'),
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
                return new JobModule(
                    $this->serviceLocator->get('Magento\Setup\Console\Command\ModuleEnableCommand'),
                    $objectManagerProvider,
                    $streamOutput,
                    $cronStatus,
                    $name,
                    $params
                );
                break;
            case self::JOB_MODULE_DISABLE:
                return new JobModule(
                    $this->serviceLocator->get('Magento\Setup\Console\Command\ModuleDisableCommand'),
                    $objectManagerProvider,
                    $streamOutput,
                    $cronStatus,
                    $name,
                    $params
                );
                break;
            case self::JOB_ENABLE_CACHE:
                $cmd = $objectManager->get('Magento\Backend\Console\Command\CacheEnableCommand');
                return new JobSetCache($cmd, $objectManagerProvider, $streamOutput, $cronStatus, $name, $params);
                break;
            case self::JOB_DISABLE_CACHE:
                $cmd = $objectManager->get('Magento\Backend\Console\Command\CacheDisableCommand');
                return new JobSetCache($cmd, $objectManagerProvider, $streamOutput, $cronStatus, $name);
                break;
            default:
                throw new \RuntimeException(sprintf('"%s" job is not supported.', $name));
                break;
        }
    }
}
