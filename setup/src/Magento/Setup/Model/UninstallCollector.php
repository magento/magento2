<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Module\FullModuleList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Setup\Module\DataSetupFactory;

/**
 * Class for collecting all Uninstall interfaces in all modules
 * @since 2.0.0
 */
class UninstallCollector
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * DataSetup Factory
     *
     * @var DataSetupFactory
     * @since 2.0.0
     */
    private $dataSetupFactory;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param DataSetupFactory $dataSetupFactory
     * @since 2.0.0
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider,
        DataSetupFactory $dataSetupFactory
    ) {
        $this->objectManager = $objectManagerProvider->get();
        $this->dataSetupFactory = $dataSetupFactory;
    }

    /**
     * Collect Uninstall classes from modules
     *
     * @param array $filterModules
     * @return UninstallInterface[]
     * @since 2.0.0
     */
    public function collectUninstall($filterModules = [])
    {
        $uninstallList = [];
        /** @var \Magento\Setup\Module\DataSetup $setup */
        $setup = $this->dataSetupFactory->create();
        $result = $setup->getConnection()->select()->from($setup->getTable('setup_module'), ['module']);
        if (isset($filterModules) && sizeof($filterModules) > 0) {
            $result->where('module in( ? )', $filterModules);
        }
        // go through modules
        foreach ($setup->getConnection()->fetchAll($result) as $row) {
            $uninstallClassName = str_replace('_', '\\', $row['module']) . '\Setup\Uninstall';
            if (class_exists($uninstallClassName)) {
                $uninstallClass = $this->objectManager->create($uninstallClassName);
                if (is_subclass_of($uninstallClass, \Magento\Framework\Setup\UninstallInterface::class)) {
                    $uninstallList[$row['module']] = $uninstallClass;
                }
            }
        }

        return $uninstallList;
    }
}
