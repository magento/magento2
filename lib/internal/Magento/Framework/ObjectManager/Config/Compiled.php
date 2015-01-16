<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        if (isset($this->virtualTypes[$instanceName])) {
            return $this->virtualTypes[$instanceName];
        }
        return $instanceName;
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
        if (isset($this->preferences[$type])) {
            return $this->preferences[$type];
        }
        return $type;
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
