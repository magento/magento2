<?php
/**
 * Application module updater. Used to install/upgrade module schemas.
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Module;

use Magento\Framework\App\State;

class Updater
{
    /**
     * Flag which allow run data install/upgrade
     *
     * @var bool
     */
    protected $_isUpdatedSchema = false;

    /**
     * Application state model
     *
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

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
     * @var \Magento\Framework\Module\Manager
     */
    private $_moduleManager;

    /**
     * @param Updater\SetupFactory $setupFactory
     * @param State $appState
     * @param ModuleListInterface $moduleList
     * @param ResourceResolverInterface $resourceResolver
     * @param Manager $moduleManager
     */
    public function __construct(
        Updater\SetupFactory $setupFactory,
        State $appState,
        ModuleListInterface $moduleList,
        ResourceResolverInterface $resourceResolver,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->_appState = $appState;
        $this->_moduleList = $moduleList;
        $this->_resourceResolver = $resourceResolver;
        $this->_setupFactory = $setupFactory;
        $this->_moduleManager = $moduleManager;
    }

    /**
     * Apply database scheme updates whenever needed
     *
     * @return void
     */
    public function updateScheme()
    {
        \Magento\Framework\Profiler::start('apply_db_schema_updates');
        $this->_appState->setUpdateMode(true);

        $afterApplyUpdates = array();
        foreach (array_keys($this->_moduleList->getModules()) as $moduleName) {
            foreach ($this->_resourceResolver->getResourceList($moduleName) as $resourceName) {
                if (!$this->_moduleManager->isDbSchemaUpToDate($moduleName, $resourceName)) {
                    $setup = $this->_setupFactory->create($resourceName, $moduleName);
                    $setup->applyUpdates();

                    if ($setup->getCallAfterApplyAllUpdates()) {
                        $afterApplyUpdates[] = $setup;
                    }
                }
            }
        }

        /** @var $setup \Magento\Framework\Module\Updater\SetupInterface*/
        foreach ($afterApplyUpdates as $setup) {
            $setup->afterApplyAllUpdates();
        }

        $this->_appState->setUpdateMode(false);
        $this->_isUpdatedSchema = true;
        \Magento\Framework\Profiler::stop('apply_db_schema_updates');
    }

    /**
     * Apply database data updates whenever needed
     *
     * @return void
     */
    public function updateData()
    {
        foreach (array_keys($this->_moduleList->getModules()) as $moduleName) {
            foreach ($this->_resourceResolver->getResourceList($moduleName) as $resourceName) {
                if (!$this->_moduleManager->isDbDataUpToDate($moduleName, $resourceName)) {
                    $this->_setupFactory->create($resourceName, $moduleName)->applyDataUpdates();
                }
            }
        }
    }
}
