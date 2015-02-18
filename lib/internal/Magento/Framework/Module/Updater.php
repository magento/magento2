<?php
/**
 * Application module updater. Used to install/upgrade module data.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Setup\Module\DataSetup;

class Updater
{
    /**
     * @var Updater\SetupFactory
     */
    protected $setupFactory;

    /**
     * @var DataSetup
     */
    private $dataSetup;

    /**
     * @var ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var DbVersionInfo
     */
    private $_dbVersionInfo;

    /**
     * Updater\SetupFactory $setupFactory
     * @param DataSetup $dataSetup
     * @param ModuleListInterface $moduleList
     * @param DbVersionInfo $dbVersionInfo
     */
    public function __construct(
        Updater\SetupFactory $setupFactory,
        DataSetup $dataSetup,
        ModuleListInterface $moduleList,
        DbVersionInfo $dbVersionInfo
    ) {
        $this->setupFactory = $setupFactory;
        $this->dataSetup = $dataSetup;
        $this->_moduleList = $moduleList;
        $this->_dbVersionInfo = $dbVersionInfo;
    }

    /**
     * Apply database data updates whenever needed
     *
     * @param \Magento\Framework\Module\Resource $resource
     * @return void
     */
    public function updateData($resource)
    {
        foreach ($this->_moduleList->getNames() as $moduleName) {
            if (!$this->_dbVersionInfo->isDataUpToDate($moduleName)) {
                $dataVer = $resource->getDataVersion($moduleName);
                $moduleConfig = $this->_moduleList->getOne($moduleName);
                $configVer = $moduleConfig['setup_version'];
                if ($dataVer !== false) {
                    $status = version_compare($configVer, $dataVer);
                    if ($status == \Magento\Framework\Setup\ModuleDataSetupInterface::VERSION_COMPARE_GREATER) {
                        $moduleContext = new \Magento\Setup\Model\ModuleContext($dataVer);
                        $moduleUpgrader = $this->setupFactory->create($moduleName, 'upgrade');
                        if ($moduleUpgrader) {
                            $moduleUpgrader->upgrade($this->dataSetup, $moduleContext);
                            $resource->setDataVersion($moduleName, $configVer);
                        }
                    }
                } elseif ($configVer) {
                    $moduleContext = new \Magento\Setup\Model\ModuleContext('');
                    $moduleInstaller = $this->setupFactory->create($moduleName, 'install');
                    if ($moduleInstaller) {
                        $moduleInstaller->install($this->dataSetup, $moduleContext);
                    }
                    $resource->setDataVersion($moduleName, $configVer);
                }
            }
        }
    }
}
