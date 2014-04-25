<?php
/**
 * Profiler driver factory
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Profiler\Driver;

use Magento\Framework\Profiler\DriverInterface;

class Factory
{
    /**
     * Default driver type
     *
     * @var string
     */
    protected $_defaultDriverType;

    /**
     * Default driver class prefix
     *
     * @var string
     */
    protected $_defaultDriverPrefix;

    /**
     * Constructor
     *
     * @param string $defaultDriverPrefix
     * @param string $defaultDriverType
     */
    public function __construct($defaultDriverPrefix = 'Magento\Framework\Profiler\Driver\\', $defaultDriverType = 'standard')
    {
        $this->_defaultDriverPrefix = $defaultDriverPrefix;
        $this->_defaultDriverType = $defaultDriverType;
    }

    /**
     * Create instance of profiler driver
     *
     * @param array $config|null
     * @return DriverInterface
     * @throws \InvalidArgumentException
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
                sprintf("Driver class \"%s\" must implement \Magento\Framework\Profiler\DriverInterface.", get_class($driver))
            );
        }
        return $driver;
    }
}
