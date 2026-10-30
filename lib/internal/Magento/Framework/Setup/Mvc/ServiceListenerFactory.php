<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Mvc;

use Laminas\ModuleManager\Listener\ServiceListener;
use Laminas\ServiceManager\ServiceManager;

/**
 * Native ServiceListenerFactory that provides minimal ServiceListener for setup application
 */
class ServiceListenerFactory
{
    /**
     * Create ServiceListener instance (minimal implementation for setup)
     *
     * @param ServiceManager $container
     * @param string $name
     * @param array|null $options
     * @return ServiceListener
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ServiceManager $container, string $name, ?array $options = null): ServiceListener
    {
        return new ServiceListener($container);
    }
}
