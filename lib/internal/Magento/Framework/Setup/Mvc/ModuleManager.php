<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Mvc;

use Laminas\EventManager\EventManagerInterface;

/**
 * Native ModuleManager that provides minimal module loading functionality for setup application
 */
class ModuleManager
{
    /**
     * @var array
     */
    private array $modules;

    /**
     * @var EventManagerInterface
     */
    private EventManagerInterface $eventManager;

    /**
     * Constructor
     *
     * @param array $modules
     * @param EventManagerInterface $eventManager
     */
    public function __construct(array $modules, EventManagerInterface $eventManager)
    {
        $this->modules = $modules;
        $this->eventManager = $eventManager;
    }

    /**
     * Load modules (loads configuration from each module)
     *
     * @return void
     */
    public function loadModules(): void
    {
        $config = [];

        // Load configuration from each module
        foreach ($this->modules as $moduleName) {
            if (class_exists($moduleName . '\Module')) {
                $moduleClass = $moduleName . '\Module';
                $moduleInstance = new $moduleClass();

                if (method_exists($moduleInstance, 'getConfig')) {
                    $moduleConfig = $moduleInstance->getConfig();
                    if (is_array($moduleConfig)) {
                        $config = array_merge_recursive($config, $moduleConfig);
                    }
                }
            }
        }

        // Trigger event to allow modules to be loaded
        $this->eventManager->trigger('loadModules.post', $this, ['config' => $config]);
    }
}
