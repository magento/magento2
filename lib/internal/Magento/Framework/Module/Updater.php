<?php
/**
 * Application module updater. Used to install/upgrade module data.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

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
     * @return void
     */
    public function updateData()
    {
        foreach ($this->_moduleList->getNames() as $moduleName) {
            foreach ($this->_resourceResolver->getResourceList($moduleName) as $resourceName) {
                if (!$this->_dbVersionInfo->isDataUpToDate($moduleName, $resourceName)) {
                    $this->_setupFactory->create($resourceName, $moduleName)->applyDataUpdates();
                }
            }
        }
    }
}
