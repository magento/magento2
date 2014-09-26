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

use Magento\Setup\Module\Setup\ConfigFactory as DeploymentConfigFactory;
use Magento\Setup\Module\Setup\Connection\AdapterInterface;
use Magento\Setup\Module\Setup\Config;
use Magento\Setup\Model\LoggerInterface;

class SetupFactory
{
    /**
     * @var DeploymentConfigFactory
     */
    private $deploymentConfigFactory;

    /**
     * Adapter
     *
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * List of all Modules
     *
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * File Resolver
     *
     * @var Setup\FileResolver
     */
    protected $fileResolver;

    /**
     * Default Constructor
     *
     * @param DeploymentConfigFactory $deploymentConfigFactory
     * @param AdapterInterface $connection
     * @param ModuleListInterface $moduleList
     * @param Setup\FileResolver $setupFileResolver
     */
    public function __construct(
        DeploymentConfigFactory $deploymentConfigFactory,
        AdapterInterface $connection,
        ModuleListInterface $moduleList,
        Setup\FileResolver $setupFileResolver
    ) {
        $this->deploymentConfigFactory = $deploymentConfigFactory;
        $this->adapter = $connection;
        $this->moduleList = $moduleList;
        $this->fileResolver = $setupFileResolver;
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
            $this->adapter,
            $this->fileResolver,
            $log,
            $this->loadConfigData()
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
        $configData = $this->loadConfigData();
        $result = new SetupModule(
            $this->adapter,
            $this->moduleList,
            $this->fileResolver,
            $log,
            $moduleName,
            $configData
        );
        if (isset($configData[Config::KEY_DB_PREFIX])) {
            $result->setTablePrefix($configData[Config::KEY_DB_PREFIX]);
        }
        return $result;
    }

    /**
     * Load deployment configuration data
     *
     * @return array
     */
    private function loadConfigData()
    {
        $config = $this->deploymentConfigFactory->create();
        $config->loadFromFile();
        return $config->getConfigData();
    }
}
