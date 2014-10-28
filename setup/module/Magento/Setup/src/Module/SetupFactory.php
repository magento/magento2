<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Setup\Module;

use Zend\ServiceManager\ServiceLocatorInterface;
use Magento\Setup\Module\Setup\ConfigFactory as DeploymentConfigFactory;
use Magento\Setup\Module\Setup\Config;
use Magento\Setup\Model\LoggerInterface;

class SetupFactory
{
    /**
     * ZF service locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Deployment config factory
     *
     * @var DeploymentConfigFactory
     */
    private $deploymentConfigFactory;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param DeploymentConfigFactory $deploymentConfigFactory
     */
    public function __construct(
        ServiceLocatorInterface $serviceLocator,
        DeploymentConfigFactory $deploymentConfigFactory
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->deploymentConfigFactory = $deploymentConfigFactory;
    }

    /**
     * Creates Setup
     *
     * @param LoggerInterface $log
     * @return Setup
     */
    public function createSetup(LoggerInterface $log)
    {
        return new Setup(
            $this->serviceLocator->get('Magento\Setup\Module\Setup\ConnectionFactory'),
            $log,
            $this->loadConfig()
        );
    }

    /**
     * Creates SetupModule
     *
     * @param LoggerInterface $log
     * @param string $moduleName
     * @return SetupModule
     */
    public function createSetupModule(LoggerInterface $log, $moduleName)
    {
        return new SetupModule(
            $this->serviceLocator->get('Magento\Setup\Module\Setup\ConnectionFactory'),
            $log,
            $this->loadConfig(),
            $this->serviceLocator->get('Magento\Setup\Module\ModuleList'),
            $this->serviceLocator->get('Magento\Setup\Module\Setup\FileResolver'),
            $moduleName
        );
    }

    /**
     * Load deployment configuration data
     *
     * @return Config
     */
    private function loadConfig()
    {
        $config = $this->deploymentConfigFactory->create();
        $config->loadFromFile();
        return $config;
    }
}
