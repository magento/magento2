<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Filesystem;

/**
 * A pool of stream wrappers
 */
class DriverPool
{
    /**#@+
     * Available driver types
     */
    const FILE = 'file';
    const HTTP = 'http';
    const HTTPS = 'https';
    const ZLIB = 'compress.zlib';
    /**#@- */

    /**
     * Supported types
     *
     * @var string[]
     */
    protected $types = [
        self::FILE => \Magento\Framework\Filesystem\Driver\File::class,
        self::HTTP => \Magento\Framework\Filesystem\Driver\Http::class,
        self::HTTPS => \Magento\Framework\Filesystem\Driver\Https::class,
        self::ZLIB => \Magento\Framework\Filesystem\Driver\Zlib::class,
    ];

    /**
     * The pool
     *
     * @var DriverInterface[]
     */
    private $pool = [];

    /**
     * Obtain extra types in constructor
     *
     * @param array $extraTypes
     * @throws \InvalidArgumentException
     */
    public function __construct($extraTypes = [])
    {
        foreach ($extraTypes as $code => $typeOrObject) {
            if (is_object($typeOrObject)) {
                $type = get_class($typeOrObject);
                $object = $typeOrObject;
            } else {
                $type = $typeOrObject;
                $object = false;
            }
            if (!is_subclass_of($type, \Magento\Framework\Filesystem\DriverInterface::class)) {
                throw new \InvalidArgumentException("The specified type '{$type}' does not implement DriverInterface.");
            }
            $this->types[$code] = $type;
            if ($object) {
                $this->pool[$code] = $typeOrObject;
            }
        }
    }

    /**
     * Gets a driver instance by code
     *
     * @param string $code
     * @return DriverInterface
     */
    public function getDriver($code)
    {
        if (!isset($this->types[$code])) {
            $code = self::FILE;
        }
        if (!isset($this->pool[$code])) {
            $class = $this->types[$code];
            $this->pool[$code] = new $class();
        }
        return $this->pool[$code];
    }
}
