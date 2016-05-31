<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DriverPool;

/**
 * Class ObjectManagerFactory
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
    protected $_locatorClassName = 'Magento\TestFramework\ObjectManager';

    /**
     * Config class name
     *
     * @var string
     */
    protected $_configClassName = 'Magento\TestFramework\ObjectManager\Config';

    /**
     * @var string
     */
    protected $envFactoryClassName = 'Magento\TestFramework\App\EnvironmentFactory';

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
        \Magento\TestFramework\ObjectManager::setInstance($objectManager);
        $this->directoryList = $directoryList;
        $objectManager->configure($this->_primaryConfigData);
        $objectManager->addSharedInstance($this->directoryList, 'Magento\Framework\App\Filesystem\DirectoryList');
        $objectManager->addSharedInstance($this->directoryList, 'Magento\Framework\Filesystem\DirectoryList');
        $deploymentConfig = $this->createDeploymentConfig($directoryList, $this->configFilePool, $arguments);
        $this->factory->setArguments($arguments);
        $objectManager->addSharedInstance($deploymentConfig, 'Magento\Framework\App\DeploymentConfig');
        $objectManager->addSharedInstance(
            $objectManager->get(
                'Magento\Framework\App\ObjectManager\ConfigLoader'
            ),
            'Magento\Framework\ObjectManager\ConfigLoaderInterface'
        );
        $objectManager->get('Magento\Framework\Interception\PluginListInterface')->reset();
        $objectManager->configure(
            $objectManager->get('Magento\Framework\App\ObjectManager\ConfigLoader')->load('global')
        );

        return $objectManager;
    }

    /**
     * Load primary config
     *
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
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
                    'default_setup' => ['type' => 'Magento\TestFramework\Db\ConnectionAdapter']
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
