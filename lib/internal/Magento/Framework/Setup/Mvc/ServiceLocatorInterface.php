<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Mvc;

/**
 * Service locator interface for setup MVC components
 * Provides compatibility with Laminas ServiceManager ServiceLocatorInterface
 */
interface ServiceLocatorInterface
{
    /**
     * Retrieve a service from the manager by name
     *
     * @param string $name
     * @return mixed
     */
    public function get(string $name): mixed;

    /**
     * Test for whether or not a service is registered in the manager
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Build a service by its name, using optional options (such as from plugin managers)
     *
     * @param string $name
     * @param array|null $options
     * @return mixed
     */
    public function build(string $name, ?array $options = null): mixed;
}
