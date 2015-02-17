<?php
/**
 * Application module updater. Used to install/upgrade module data.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Setup\Module\ModuleInstallerUpgraderFactory;

class Updater
{
    /**
     * @var ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var ResourceResolverInterface
     */
    protected $_resourceResolver;

    /**
     * @var Updater\SetupFactory
     */
    protected $_setupFactory;

    /**
     * @var DbVersionInfo
     */
    private $_dbVersionInfo;

    /**
     * @param Updater\SetupFactory $setupFactory
     * @param ModuleListInterface $moduleList
     * @param ResourceResolverInterface $resourceResolver
     * @param DbVersionInfo $dbVersionInfo
     */
    public function __construct(
        Updater\SetupFactory $setupFactory,
        ModuleListInterface $moduleList,
        ResourceResolverInterface $resourceResolver,
        DbVersionInfo $dbVersionInfo
    ) {
        $this->_moduleList = $moduleList;
        $this->_resourceResolver = $resourceResolver;
        $this->_setupFactory = $setupFactory;
        $this->_dbVersionInfo = $dbVersionInfo;
    }

    /**
     * Apply database data updates whenever needed
     *
     * @param \Magento\Framework\Module\Resource $resource
     * @param ModuleInstallerUpgraderFactory $moduleInstallerUpgraderFactory
     * @return void
     */
    public function updateData($resource, $moduleInstallerUpgraderFactory)
    {
        foreach ($this->_moduleList->getNames() as $moduleName) {
            foreach ($this->_resourceResolver->getResourceList($moduleName) as $resourceName) {
                if (!$this->_dbVersionInfo->isDataUpToDate($moduleName, $resourceName)) {
                    $moduleDataResource = $this->_setupFactory->create($resourceName, $moduleName);
                    $dataVer = $resource->getDataVersion($moduleName);
                    $moduleConfig = $this->_moduleList->getOne($moduleName);
                    $configVer = $moduleConfig['setup_version'];
                    $moduleContext = new \Magento\Setup\Model\ModuleContext($dataVer);
                    if ($dataVer !== false) {
                        $status = version_compare($configVer, $dataVer);
                        if ($status == \Magento\Framework\Setup\ModuleDataResourceInterface::VERSION_COMPARE_GREATER) {
                            $moduleUpgrader = $moduleInstallerUpgraderFactory->createDataUpgrader($moduleName);
                            if ($moduleUpgrader) {
                                $moduleUpgrader->upgrade($moduleDataResource, $moduleContext);
                                $resource->setDataVersion($moduleName, $configVer);
                            }
                        }
                    } elseif ($configVer) {
                        $moduleInstaller = $moduleInstallerUpgraderFactory->createDataInstaller($moduleName);
                        if ($moduleInstaller) {
                            $moduleInstaller->install($moduleDataResource, $moduleContext);
                            $resource->setDataVersion($moduleName, $configVer);
                        }
                    }
                }
            }
        }
    }
}
