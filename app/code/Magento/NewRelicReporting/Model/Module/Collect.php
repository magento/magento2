<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Module;

use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\Manager;
use Magento\Framework\Module\ModuleListInterface;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\Module;

class Collect
{
    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var fullModuleList
     */
    protected $fullModuleList;

    /**
     * @var \Magento\NewRelicReporting\Model\ModuleFactory
     */
    protected $moduleFactory;

    /**
     * @var \Magento\NewRelicReporting\Model\ResourceModel\Module\CollectionFactory
     */
    protected $moduleCollectionFactory;

    /**
     * Constructor
     *
     * @param ModuleListInterface $moduleList
     * @param FullModuleList $fullModuleList
     * @param Manager $moduleManager
     * @param \Magento\NewRelicReporting\Model\ModuleFactory $moduleFactory
     * @param \Magento\NewRelicReporting\Model\ResourceModel\Module\CollectionFactory $moduleCollectionFactory
     */
    public function __construct(
        ModuleListInterface $moduleList,
        FullModuleList $fullModuleList,
        Manager $moduleManager,
        \Magento\NewRelicReporting\Model\ModuleFactory $moduleFactory,
        \Magento\NewRelicReporting\Model\ResourceModel\Module\CollectionFactory $moduleCollectionFactory
    ) {
        $this->moduleList = $moduleList;
        $this->fullModuleList = $fullModuleList;
        $this->moduleManager = $moduleManager;
        $this->moduleFactory = $moduleFactory;
        $this->moduleCollectionFactory = $moduleCollectionFactory;
    }

    /**
     * Retrieve all disabled modules from the configuration
     *
     * @return array
     */
    protected function getDisabledModules()
    {
        $allModules = $this->fullModuleList->getNames();
        $enabledModules = $this->moduleList->getNames();
        $disabledModules = array_diff($allModules, $enabledModules);

        return $disabledModules;
    }

    /**
     * Retrieve all modules array
     *
     * @return array
     */
    protected function getAllModules()
    {
        return $this->fullModuleList->getAll();
    }

    /**
     * Get changes of module not in DB
     *
     * @param string $moduleName
     * @param string $active
     * @param string $setupVersion
     * @param string $state
     *
     * @return array
     */
    protected function getNewModuleChanges($moduleName, $active, $setupVersion, $state)
    {
        /** @var \Magento\NewRelicReporting\Model\Module $newModule */
        $newModule = $this->moduleFactory->create();
        $data = [
            'name'          =>  $moduleName,
            'active'        =>  $active,
            'setup_version' =>  $setupVersion,
            'state'         =>  $state,
        ];

        $newModule->setData($data);
        $newModule->save();
        $moduleChanges = [
            'name' => $data['name'],
            'setup_version' => $data['setup_version'],
            'type' => Config::INSTALLED
        ];

        return $moduleChanges;
    }

    /**
     * Grabs the collection items to get final counts
     *
     * @param array $moduleChanges
     * @return array
     */
    protected function getFinalCounts($moduleChanges)
    {
        /** @var Module[] $finalDbModuleArray */
        $finalDbModuleArray = $this->moduleCollectionFactory->create()->getItems();

        $stateCallback = function (Module $value) {
            return $value->getState();
        };

        $stateValues = array_map($stateCallback, $finalDbModuleArray);
        $installedCount = count($stateValues);
        $disabledCount = $enabledCount = $uninstalledCount = 0;

        foreach ($stateValues as $state) {
            switch ($state) {
                case Config::ENABLED:
                    $enabledCount++;
                    break;

                case Config::DISABLED:
                    $disabledCount++;
                    break;

                case Config::UNINSTALLED:
                    $uninstalledCount++;
                    break;
            }
        }

        $installedCount -= $uninstalledCount;

        $finalObject = [
            Config::INSTALLED => $installedCount,
            Config::UNINSTALLED => $uninstalledCount,
            Config::ENABLED => $enabledCount,
            Config::DISABLED => $disabledCount,
            'changes' => $moduleChanges
        ];

        return $finalObject;
    }

    /**
     * Get module state by it name
     *
     * @param string $moduleName
     * @return string
     */
    protected function getState($moduleName)
    {
        if ($this->moduleManager->isOutputEnabled($moduleName)) {
            $state = Config::ENABLED;
        } else {
            $state = Config::DISABLED;
        }

        return $state;
    }

    /**
     * Get module active state by it name
     *
     * @param string $moduleName
     * @return string
     */
    protected function getActive($moduleName)
    {
        if (in_array($moduleName, $this->getDisabledModules())) {
            $active = Config::FALSE;
        } else {
            $active = Config::TRUE;
        }

        return $active;
    }

    /**
     * Get clean array of changes
     *
     * @param array $changes
     * @return array mixed
     */
    protected function getCleanChangesArray($changes)
    {
        $changesArrayKeys = array_keys($changes);
        foreach ($changesArrayKeys as $changesKey) {
            if ($changesKey != 'state' && $changesKey != 'active' && $changesKey != 'setup_version') {
                unset($changes[$changesKey]);
            }
        }

        return $changes;
    }

    /**
     * Takes module changes if module were uninstalled
     *
     * @param Module[] $dbModuleArray
     * @param string[] $arrayModuleNames
     * @return array|bool
     */
    protected function setUninstalledModuleChanges(array $dbModuleArray, array $arrayModuleNames)
    {
        foreach ($dbModuleArray as $module) {
            /** @var Module $module */
            if (!in_array($module->getName(), $arrayModuleNames) && $module->getState() != Config::UNINSTALLED) {
                $moduleChanges = [
                    'name' => $module->getName(),
                    'setup_version' => $module->getSetupVersion(),
                    'type' => Config::UNINSTALLED
                ];
                $module->setData(['entity_id' => $module->getEntityId(), 'state' => Config::UNINSTALLED]);
                $module->save();
                return $moduleChanges;
            }
        }

        return false;
    }

    /**
     * Collects required data about the modules
     *
     * @param bool $refresh
     * @return array
     */
    public function getModuleData($refresh = true)
    {
        $callback = function (Module $value) {
            return $value->getName();
        };

        $configModules = $this->getAllModules();
        /** @var Module[] $dbModuleArray */
        $dbModuleArray = $this->moduleCollectionFactory->create()->getItems();

        $nameValues = array_map($callback, $dbModuleArray);
        $moduleChanges = [];

        foreach ($configModules as $moduleName => $module) {
            unset($module['sequence']);
            $state = $this->getState($moduleName);
            $active = $this->getActive($moduleName);
            $module['state'] = $state;
            $module['active'] = $active;

            if (!in_array($moduleName, $nameValues)) {
                $moduleChanges[] = $this->getNewModuleChanges($moduleName, $active, $module['setup_version'], $state);
            } else {
                $dbModule = $dbModuleArray[array_search($moduleName, $nameValues)];
                $changeTest = $dbModule->getData();
                $changes = array_diff($module, $changeTest);
                $changesCleanArray = $this->getCleanChangesArray($changes);

                if (count($changesCleanArray) > 0 ||
                    ($this->moduleManager->isOutputEnabled($changeTest['name']) &&
                        $module['setup_version'] != null)) {
                    $data = [
                        'entity_id' => $changeTest['entity_id'],
                        'name' => $changeTest['name'],
                        'active' => $active,
                        'setup_version' => $module['setup_version'],
                        'state' => $state,
                    ];
                    if ($refresh) {
                        $dbModule->setData($data);
                        $dbModule->save();
                    }
                    $moduleChanges[] = [
                        'name' => $data['name'],
                        'setup_version' => $data['setup_version'],
                        'type' => $state
                    ];
                }
            }
        }

        $arrayModuleNames = array_keys($configModules);
        $uninstalledModuleChanges = $this->setUninstalledModuleChanges($dbModuleArray, $arrayModuleNames);
        if (is_array($uninstalledModuleChanges)) {
            $moduleChanges[] = $uninstalledModuleChanges;
        }

        $finalObject = $this->getFinalCounts($moduleChanges);

        return $finalObject;
    }
}
