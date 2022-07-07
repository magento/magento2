<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver;

use Magento\Framework\Exception\RuntimeException;

/**
 * Pool of driver factories.
 */
class DriverFactoryPool
{
    /**
     * @var DriverFactoryInterface[]
     */
    private $pool;

    /**
     * @param DriverFactoryInterface[] $pool
     */
    public function __construct(array $pool = [])
    {
        $this->pool = $pool;
    }

    /**
     * Check if factory exists.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->pool[$name]);
    }

    /**
     * Retrieve factory.
     *
     * @param string $name
     * @return DriverFactoryInterface
     *
     * @throws RuntimeException
     */
    public function get(string $name): DriverFactoryInterface
    {
        if (!$this->has($name)) {
            throw new RuntimeException(__('Driver "%1" does not exist', $name));
        }

        return $this->pool[$name];
    }
}
