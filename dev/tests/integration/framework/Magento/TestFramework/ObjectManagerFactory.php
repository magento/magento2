<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Interception\PluginListInterface;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\TestFramework\App\EnvironmentFactory;
use Magento\TestFramework\Db\ConnectionAdapter;

/**
 * Configure ObjectManagerFactory for testing purpose
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObjectManagerFactory extends \Magento\Framework\App\ObjectManagerFactory
{
    /**
     * Locator class name
     *
     * @var string
     */
    protected $_locatorClassName = ObjectManager::class;

    /**
     * Config class name
     *
     * @var string
     */
    protected $_configClassName = \Magento\TestFramework\ObjectManager\Config::class;

    /**
     * @var string
     */
    protected $envFactoryClassName = EnvironmentFactory::class;

    /**
     * @var array
     */
    protected $_primaryConfigData = null;

    /**
     * Restore locator instance
     *
     * @param ObjectManager $objectManager
     * @param DirectoryList $directoryList
     * @param array $arguments
     * @return ObjectManager
     */
    public function restore(ObjectManager $objectManager, $directoryList, array $arguments)
    {
        ObjectManager::setInstance($objectManager);
        $this->directoryList = $directoryList;
        $objectManager->configure($this->_primaryConfigData);
        $objectManager->addSharedInstance($this->directoryList, DirectoryList::class);
        $objectManager->addSharedInstance(
            $this->directoryList,
            \Magento\Framework\Filesystem\DirectoryList::class
        );
        $deploymentConfig = $this->createDeploymentConfig($directoryList, $this->configFilePool, $arguments);
        $this->factory->setArguments($arguments);
        $objectManager->addSharedInstance($deploymentConfig, DeploymentConfig::class);
        $objectManager->addSharedInstance(
            $objectManager->get(ConfigLoader::class),
            ConfigLoaderInterface::class,
            true
        );
        $objectManager->get(PluginListInterface::class)->reset();
        $objectManager->configure(
            $objectManager->get(ConfigLoader::class)->load('global')
        );

        return $objectManager;
    }

    /**
     * Load primary config
     *
     * @param DirectoryList $directoryList
     * @param DriverPool $driverPool
     * @param mixed $argumentMapper
     * @param string $appMode
     * @return array
     */
    protected function _loadPrimaryConfig(DirectoryList $directoryList, $driverPool, $argumentMapper, $appMode)
    {
        if (null === $this->_primaryConfigData) {
            $this->_primaryConfigData = array_replace(
                parent::_loadPrimaryConfig($directoryList, $driverPool, $argumentMapper, $appMode),
                [
                    'default_setup' => ['type' => ConnectionAdapter::class]
                ]
            );
            $diPreferences = [];
            $diPreferencesPath = __DIR__ . '/../../../etc/di/preferences/';

            $preferenceFiles = glob($diPreferencesPath . '*.php');

            foreach ($preferenceFiles as $file) {
                if (!is_readable($file)) {
                    throw new LocalizedException(__("'%1' is not readable file.", $file));
                }
                $diPreferences = array_replace($diPreferences, include $file);
            }

            $this->_primaryConfigData['preferences'] = array_replace(
                $this->_primaryConfigData['preferences'],
                $diPreferences
            );
        }
        return $this->_primaryConfigData;
    }
}
