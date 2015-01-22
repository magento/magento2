<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

class StatusFactory
{
    /**
     * Module List
     *
     * @var ModuleList
     */
    private $moduleList;

    /**
     * Object Manager Factory
     *
     * @var ObjectManagerFactory
     */
    private $objectManagerFactory;

    /**
     * @param ModuleList $moduleList
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(ModuleList $moduleList, ObjectManagerFactory $objectManagerFactory)
    {
        $this->moduleList = $moduleList;
        $this->objectManagerFactory = $objectManagerFactory;
    }

    /**
     * Create Status object
     *
     * @return \Magento\Framework\Module\Status
     */
    public function create()
    {
        $objectManager = $this->objectManagerFactory->create();
        $reader = $objectManager->create('Magento\Framework\Module\Dir\Reader', ['moduleList' => $this->moduleList]);
        $packageInfo = $objectManager->create('Magento\Framework\Module\PackageInfo', ['reader' => $reader]);
        $conflictChecker = $objectManager->create(
            'Magento\Framework\Module\ConflictChecker',
            ['packageInfo' => $packageInfo]
        );
        $dependencyChecker = $objectManager->create(
            'Magento\Framework\Module\DependencyChecker',
            ['packageInfo' => $packageInfo]
        );
        return $objectManager->create(
            'Magento\Framework\Module\Status',
            ['conflictChecker' => $conflictChecker, 'dependencyChecker' => $dependencyChecker]
        );
    }
}
