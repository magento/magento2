<?php
/**
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
namespace Magento\Framework\ObjectManager\Config;

use Magento\Framework\ObjectManager\ConfigCacheInterface;
use Magento\Framework\ObjectManager\RelationsInterface;

class Compiled implements \Magento\Framework\ObjectManager\ConfigInterface
{
    /**
     * @var array
     */
    private $arguments;

    /**
     * @var array
     */
    private $nonShared;

    /**
     * @var array
     */
    private $virtualTypes;

    /**
     * @var array
     */
    private $preferences;

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        $this->arguments = $data['arguments'];
        $this->nonShared = $data['nonShared'];
        $this->virtualTypes = $data['instanceTypes'];
        $this->preferences = $data['preferences'];
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

    }

    /**
     * Retrieve list of arguments per type
     *
     * @param string $type
     * @return array
     */
    public function getArguments($type)
    {
        if (isset($this->arguments[$type])) {
            if (is_string($this->arguments[$type])) {
                $this->arguments[$type] = unserialize($this->arguments[$type]);
            }
            return $this->arguments[$type];
        } else {
            return ['Magento\Framework\ObjectManagerInterface'];
        }
    }

    /**
     * Check whether type is shared
     *
     * @param string $type
     * @return bool
     */
    public function isShared($type)
    {
        return !isset($this->nonShared[$type]);
    }

    /**
     * Retrieve instance type
     *
     * @param string $instanceName
     * @return mixed
     */
    public function getInstanceType($instanceName)
    {
        return isset($this->virtualTypes[$instanceName]) ? $this->virtualTypes[$instanceName] : $instanceName;
    }

    /**
     * Retrieve preference for type
     *
     * @param string $type
     * @return string
     * @throws \LogicException
     */
    public function getPreference($type)
    {
        return isset($this->preferences[$type]) ? $this->preferences[$type] : $type;
    }

    /**
     * Extend configuration
     *
     * @param array $configuration
     * @return void
     */
    public function extend(array $configuration)
    {
        $this->arguments = $configuration['arguments'];
        $this->nonShared = $configuration['nonShared'];
        $this->virtualTypes = $configuration['instanceTypes'];
        $this->preferences = $configuration['preferences'];
    }

    /**
     * Retrieve all virtual types
     *
     * @return string
     */
    public function getVirtualTypes()
    {
        return $this->virtualTypes;
    }
}
