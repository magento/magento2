<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\Di;

use Magento\Developer\Model\Di\PluginList;

class Information
{
    /**
     * @var \Magento\Framework\ObjectManager\ConfigInterface
     */
    private $objectManagerConfig;

    /**
     * @var \Magento\Developer\Model\Di\PluginList
     */
    private $pluginList;

    /**
     * @var string[]
     */
    private $preferences = [];

    /**
     * @var array
     */
    private $virtualTypes = [];

    /**
     * @var \Magento\Framework\ObjectManager\DefinitionInterface
     */
    private $definitions;

    /**
     * @param \Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig
     * @param \Magento\Framework\ObjectManager\DefinitionInterface $definitions
     */
    public function __construct(
        \Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig,
        \Magento\Framework\ObjectManager\DefinitionInterface $definitions,
        \Magento\Developer\Model\Di\PluginList $pluginList
    ) {
        $this->objectManagerConfig = $objectManagerConfig;
        $this->definitions = $definitions;
        $this->pluginList = $pluginList;
    }

    /**
     * Get info on the preference for the class
     *
     * @param string $className
     * @return string
     */
    public function getPreference($className)
    {
        if (!isset($this->preferences[$className])) {
            $this->preferences[$className] =  $this->objectManagerConfig->getPreference($className);
        }
        return $this->preferences[$className];
    }

    /**
     * Retrieve parameters of the constructor for the preference of the class
     *
     * @param $className
     * @return array|null
     */
    private function getConstructorParameters($className)
    {
        $parameters = $this->definitions->getParameters($className);
        return $parameters;
    }

    /**
     * Retrieve array of parameters for the class constructor
     *
     * @param $className
     * @return array
     */
    public function getParameters($className)
    {
        $result = [];
        $diConfiguration = $this->getConfiguredConstructorParameters($className);
        $originalParameters = $this->isVirtualType($className) ?
            $this->getConstructorParameters($this->getVirtualTypeBase($className)) :
            $this->getConstructorParameters($className);

        foreach ($originalParameters as $parameter) {
            $paramArray = [$parameter[0], $parameter[1], ''];
            if (isset($diConfiguration[$parameter[0]])) {
                $configuredParameter = $diConfiguration[$parameter[0]];
                if (is_array($configuredParameter)) {
                    if (isset($configuredParameter['instance'])) {
                        $paramArray[2] = $configuredParameter['instance'];
                    } else {
                        //TODO
                    }
                } else {
                    $paramArray[2] = $configuredParameter;
                }

            }
            $result[] = $paramArray;
        }
        return $result;
    }

    /**
     * Retrieve configured types of parameters of the constructor for the preference of the class
     *
     * @param $className
     * @return array|null
     */
    private function getConfiguredConstructorParameters($className)
    {
        return $this->objectManagerConfig->getArguments($className);
    }

    /**
     * Retrieve virtual types for the class and the preference of the class
     *
     * @param $className
     * @return array
     */
    public function getVirtualTypes($className)
    {
        $preference = $this->getPreference($className);
        if (!isset($this->virtualTypes[$className])) {
            $this->virtualTypes[$className] = [];
            foreach ($this->objectManagerConfig->getVirtualTypes() as $virtualType => $baseName) {
                if ($baseName == $className || $baseName == $preference) {
                    $this->virtualTypes[$className][] = $virtualType;
                }
            }
        }
        return $this->virtualTypes[$className];
    }

    /**
     * @param string $className
     * @return array
     */
    public function getPlugins($className)
    {
        return $this->pluginList->getPluginsListByClass($className);
    }

    /**
     * Is the class a virtual type
     *
     * @param string $className
     * @return boolean
     */
    public function isVirtualType($className)
    {
        $virtualTypes = $this->objectManagerConfig->getVirtualTypes();
        return isset($virtualTypes[$className]);
    }

    /**
     * Get base class for the Virtual Type
     *
     * @param string $className
     * @return string|boolean
     */
    public function getVirtualTypeBase($className)
    {
        $virtualTypes = $this->objectManagerConfig->getVirtualTypes();
        if (isset($virtualTypes[$className])) {
            return $virtualTypes[$className];
        }
        return false;
    }
}