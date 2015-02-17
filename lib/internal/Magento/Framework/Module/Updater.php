<?php
/**
 * Application module updater. Used to install/upgrade module data.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Setup\Module\ModuleInstallerUpgraderFactory;
use Magento\Setup\Module\DataSetup;

class Updater
{
    /**
     * @var ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var DbVersionInfo
     */
    private $_dbVersionInfo;

    /**
     * @var DataSetup
     */
    private $setup;

    /**
     * @param DataSetup $setup
     * @param ModuleListInterface $moduleList
     * @param DbVersionInfo $dbVersionInfo
     */
    public function __construct(
        DataSetup $setup,
        ModuleListInterface $moduleList,
        DbVersionInfo $dbVersionInfo
    ) {
        $this->setup = $setup;
        $this->_moduleList = $moduleList;
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
            if (!$this->_dbVersionInfo->isDataUpToDate($moduleName)) {
                $dataVer = $resource->getDataVersion($moduleName);
                $moduleConfig = $this->_moduleList->getOne($moduleName);
                $configVer = $moduleConfig['setup_version'];
                $moduleContext = new \Magento\Setup\Model\ModuleContext($dataVer);
                if ($dataVer !== false) {
                    $status = version_compare($configVer, $dataVer);
                    if ($status == \Magento\Framework\Setup\ModuleDataResourceInterface::VERSION_COMPARE_GREATER) {
                        $moduleUpgrader = $moduleInstallerUpgraderFactory->createDataUpgrader($moduleName);
                        if ($moduleUpgrader) {
                            $moduleUpgrader->upgrade($this->setup, $moduleContext);
                            $resource->setDataVersion($moduleName, $configVer);
                        }
                    }
                } elseif ($configVer) {
                    $moduleInstaller = $moduleInstallerUpgraderFactory->createDataInstaller($moduleName);
                    if ($moduleInstaller) {
                        $moduleInstaller->install($this->setup, $moduleContext);
                        $resource->setDataVersion($moduleName, $configVer);
                    }
                }
            }
        }
    }
}
