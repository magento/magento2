<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
        $cronStatus = $this->serviceLocator->get(\Magento\Setup\Model\Cron\Status::class);
        $statusStream = fopen($cronStatus->getStatusFilePath(), 'a+');
        $logStream = fopen($cronStatus->getLogFilePath(), 'a+');
        $multipleStreamOutput = new MultipleStreamOutput([$statusStream, $logStream]);
        $objectManagerProvider = $this->serviceLocator->get(\Magento\Setup\Model\ObjectManagerProvider::class);
        /** @var \Magento\Framework\ObjectManagerInterface $objectManager */
        $objectManager = $objectManagerProvider->get();
        switch ($name) {
            case self::JOB_UPGRADE:
                return new JobUpgrade(
                    $this->serviceLocator->get(\Magento\Setup\Console\Command\UpgradeCommand::class),
                    $objectManagerProvider,
                    $multipleStreamOutput,
                    $this->serviceLocator->get(\Magento\Setup\Model\Cron\Queue::class),
                    $cronStatus,
                    $name,
                    $params
                );
                break;
            case self::JOB_DB_ROLLBACK:
                return new JobDbRollback(
                    $objectManager->get(\Magento\Framework\Setup\BackupRollbackFactory::class),
                    $multipleStreamOutput,
                    $cronStatus,
                    $objectManagerProvider,
                    $name,
                    $params
                );
                break;
            case self::JOB_STATIC_REGENERATE:
                return new JobStaticRegenerate(
                    $objectManagerProvider,
                    $multipleStreamOutput,
                    $cronStatus,
                    $name,
                    $params
                );
                break;
            case self::JOB_COMPONENT_UNINSTALL:
                $moduleUninstall = new Helper\ModuleUninstall(
                    $this->serviceLocator->get(\Magento\Setup\Model\ModuleUninstaller::class),
                    $this->serviceLocator->get(\Magento\Setup\Model\ModuleRegistryUninstaller::class),
                    $objectManager->get(\Magento\Framework\Module\PackageInfoFactory::class)
                );
                $themeUninstall = new Helper\ThemeUninstall(
                    $objectManager->get(\Magento\Theme\Model\Theme\ThemeUninstaller::class),
                    $objectManager->get(\Magento\Theme\Model\Theme\ThemePackageInfo::class)
                );
                return new JobComponentUninstall(
                    $objectManager->get(\Magento\Framework\Composer\ComposerInformation::class),
                    $moduleUninstall,
                    $themeUninstall,
                    $objectManagerProvider,
                    $multipleStreamOutput,
                    $this->serviceLocator->get(\Magento\Setup\Model\Cron\Queue::class),
                    $cronStatus,
                    $this->serviceLocator->get(\Magento\Setup\Model\Updater::class),
                    $name,
                    $params
                );
                break;
            case self::JOB_MODULE_ENABLE:
                return new JobModule(
                    $this->serviceLocator->get(\Magento\Setup\Console\Command\ModuleEnableCommand::class),
                    $objectManagerProvider,
                    $multipleStreamOutput,
                    $cronStatus,
                    $name,
                    $params
                );
                break;
            case self::JOB_MODULE_DISABLE:
                return new JobModule(
                    $this->serviceLocator->get(\Magento\Setup\Console\Command\ModuleDisableCommand::class),
                    $objectManagerProvider,
                    $multipleStreamOutput,
                    $cronStatus,
                    $name,
                    $params
                );
                break;
            default:
                throw new \RuntimeException(sprintf('"%s" job is not supported.', $name));
                break;
        }
    }
}
