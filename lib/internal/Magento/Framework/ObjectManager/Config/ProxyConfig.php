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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\ObjectManager\Config;

use Magento\Framework\ObjectManager\ConfigCacheInterface;
use Magento\Framework\ObjectManager\RelationsInterface;

class ProxyConfig implements \Magento\Framework\ObjectManager\ConfigInterface
{
    /**
     * @var \Magento\Framework\ObjectManager\ConfigInterface
     */
    protected $subjectConfig;

    /**
     * @param \Magento\Framework\ObjectManager\ConfigInterface $config
     */
    public function __construct(\Magento\Framework\ObjectManager\ConfigInterface $config)
    {
        $this->subjectConfig = $config;
    }

    /**
     * Set class relations
     *
     * @param RelationsInterface $relations
     *
     * @return void
     */
    public function setRelations(RelationsInterface $relations)
    {
        $this->subjectConfig->setRelations($relations);
    }

    /**
     * Set configuration cache instance
     *
     * @param ConfigCacheInterface $cache
     *
     * @return void
     */
    public function setCache(ConfigCacheInterface $cache)
    {
        $this->subjectConfig->setCache($cache);
    }

    /**
     * Retrieve list of arguments per type
     *
     * @param string $type
     *
     * @return array
     */
    public function getArguments($type)
    {
        return $this->subjectConfig->getArguments($type);
    }

    /**
     * Check whether type is shared
     *
     * @param string $type
     *
     * @return bool
     */
    public function isShared($type)
    {
        return $this->subjectConfig->isShared($type);
    }

    /**
     * Retrieve instance type
     *
     * @param string $instanceName
     *
     * @return mixed
     */
    public function getInstanceType($instanceName)
    {
        return $this->subjectConfig->getInstanceType($instanceName);
    }

    /**
     * Retrieve preference for type
     *
     * @param string $type
     *
     * @return string
     * @throws \LogicException
     */
    public function getPreference($type)
    {
        return $this->subjectConfig->getPreference($type);
    }

    /**
     * Extend configuration
     *
     * @param array $configuration
     *
     * @return void
     */
    public function extend(array $configuration)
    {
        $this->subjectConfig->extend($configuration);
    }

    /**
     * Returns list of virtual types
     *
     * @return array
     */
    public function getVirtualTypes()
    {
        return $this->subjectConfig->getVirtualTypes();
    }
}
