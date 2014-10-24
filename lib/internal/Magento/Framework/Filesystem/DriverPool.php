<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        self::FILE => 'Magento\Framework\Filesystem\Driver\File',
        self::HTTP => 'Magento\Framework\Filesystem\Driver\Http',
        self::HTTPS => 'Magento\Framework\Filesystem\Driver\Https',
        self::ZLIB => 'Magento\Framework\Filesystem\Driver\Zlib',
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
            if (!is_subclass_of($type, '\Magento\Framework\Filesystem\DriverInterface')) {
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
            $this->pool[$code] = new $class;
        }
        return $this->pool[$code];
    }
}
