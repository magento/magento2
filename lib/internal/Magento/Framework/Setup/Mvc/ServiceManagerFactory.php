<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Mvc;

use Laminas\ServiceManager\ServiceLocatorInterface as LaminasServiceLocatorInterface;

/**
 * Proxy to provide the Laminas ServiceManager instance to Magento's ObjectManager
 * This bridges the gap between the setup bootstrapping (Laminas ServiceManager)
 * and command execution (Magento ObjectManager)
 */
class ServiceManagerFactory implements ServiceLocatorInterface
{
    /**
     * @var LaminasServiceLocatorInterface|null
     */
    private static ?LaminasServiceLocatorInterface $serviceManager = null;

    /**
     * Set the ServiceManager instance (called during setup bootstrap)
     *
     * @param LaminasServiceLocatorInterface $serviceManager
     */
    public static function setServiceManager(LaminasServiceLocatorInterface $serviceManager): void
    {
        self::$serviceManager = $serviceManager;
    }

    /**
     * Get a service from the ServiceManager
     *
     * @param string $name
     * @return mixed
     * @throws \RuntimeException
     */
    public function get(string $name): mixed
    {
        if (self::$serviceManager === null) {
            throw new \RuntimeException('ServiceManager not initialized. This should be set during setup bootstrap.');
        }

        return self::$serviceManager->get($name);
    }

    /**
     * Check if a service exists in the ServiceManager
     *
     * @param string $name
     * @return bool
     * @throws \RuntimeException
     */
    public function has(string $name): bool
    {
        if (self::$serviceManager === null) {
            throw new \RuntimeException('ServiceManager not initialized. This should be set during setup bootstrap.');
        }

        return self::$serviceManager->has($name);
    }

    /**
     * Build a service by its name, using optional options
     *
     * @param string $name
     * @param array|null $options
     * @return mixed
     * @throws \RuntimeException
     */
    public function build(string $name, ?array $options = null): mixed
    {
        if (self::$serviceManager === null) {
            throw new \RuntimeException('ServiceManager not initialized. This should be set during setup bootstrap.');
        }

        return self::$serviceManager->build($name, $options);
    }
}
