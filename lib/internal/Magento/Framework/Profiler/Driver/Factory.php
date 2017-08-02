<?php
/**
 * Profiler driver factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Driver;

use Magento\Framework\Profiler\DriverInterface;

/**
 * Class \Magento\Framework\Profiler\Driver\Factory
 *
 * @since 2.0.0
 */
class Factory
{
    /**
     * Default driver type
     *
     * @var string
     * @since 2.0.0
     */
    protected $_defaultDriverType;

    /**
     * Default driver class prefix
     *
     * @var string
     * @since 2.0.0
     */
    protected $_defaultDriverPrefix;

    /**
     * Constructor
     *
     * @param string $defaultDriverPrefix
     * @param string $defaultDriverType
     * @since 2.0.0
     */
    public function __construct(
        $defaultDriverPrefix = 'Magento\Framework\Profiler\Driver\\',
        $defaultDriverType = 'standard'
    ) {
        $this->_defaultDriverPrefix = $defaultDriverPrefix;
        $this->_defaultDriverType = $defaultDriverType;
    }

    /**
     * Create instance of profiler driver
     *
     * @param array $config|null
     * @return DriverInterface
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function create(array $config = null)
    {
        $type = isset($config['type']) ? $config['type'] : $this->_defaultDriverType;
        if (class_exists($type)) {
            $class = $type;
        } else {
            $class = $this->_defaultDriverPrefix . ucfirst($type);
            if (!class_exists($class)) {
                throw new \InvalidArgumentException(
                    sprintf("Cannot create profiler driver, class \"%s\" doesn't exist.", $class)
                );
            }
        }
        $driver = new $class($config);
        if (!$driver instanceof DriverInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Driver class \"%s\" must implement \Magento\Framework\Profiler\DriverInterface.",
                    get_class($driver)
                )
            );
        }
        return $driver;
    }
}
