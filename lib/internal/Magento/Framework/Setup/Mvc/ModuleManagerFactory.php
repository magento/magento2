<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Mvc;

/**
 * Native ModuleManagerFactory that creates ModuleManager for setup application
 */
class ModuleManagerFactory
{
    /**
     * Create ModuleManager instance (minimal implementation for setup)
     *
     * @param mixed $container Laminas ServiceManager
     * @param string $name
     * @param array|null $options
     * @return ModuleManager
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(mixed $container, string $name, ?array $options = null): ModuleManager
    {
        $configuration = $container->get('ApplicationConfig');
        $modules = $configuration['modules'] ?? [];
        $eventManager = $container->get('EventManager');

        return new ModuleManager($modules, $eventManager);
    }
}
