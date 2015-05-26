<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Setup\UninstallInterface;

/**
 * Class for collecting all Uninstall interfaces in all modules
 */
class UninstallCollector
{
    /**
     * Module list including enabled and disabled modules
     *
     * @var FullModuleList
     */
    private $fullModuleList;

    /**
     * Object manager provider
     *
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * Constructor
     *
     * @param FullModuleList $fullModuleList
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        FullModuleList $fullModuleList,
        ObjectManagerProvider $objectManagerProvider
    ) {
        $this->fullModuleList = $fullModuleList;
        $this->objectManagerProvider = $objectManagerProvider;
    }

    /**
     * Collect Uninstall classes from modules
     *
     * @return UninstallInterface[]
     */
    public function collectUninstall()
    {
        $uninstallList = [];

        // go through modules
        foreach ($this->fullModuleList->getNames() as $moduleName) {
            $uninstallClassName = str_replace('_', '\\', $moduleName) . '\Setup\Uninstall';
            if (class_exists($uninstallClassName)) {
                $uninstallClass = $this->objectManagerProvider->get()->create($uninstallClassName);
                if ($uninstallClassName instanceof UninstallInterface) {
                    $uninstallList[$moduleName] = $uninstallClass;
                }
            }
        }

        return $uninstallList;
    }
}
