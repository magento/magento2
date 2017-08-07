<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\Di;

/**
 * Class \Magento\Developer\Model\Di\Information
 *
 * @since 2.2.0
 */
class Information
{
    /**
     * @var \Magento\Framework\ObjectManager\ConfigInterface
     * @since 2.2.0
     */
    private $objectManagerConfig;

    /**
     * @var \Magento\Developer\Model\Di\PluginList
     * @since 2.2.0
     */
    private $pluginList;

    /**
     * @var string[]
     * @since 2.2.0
     */
    private $preferences = [];

    /**
     * @var array
     * @since 2.2.0
     */
    private $virtualTypes = [];

    /**
     * @var \Magento\Framework\ObjectManager\DefinitionInterface
     * @since 2.2.0
     */
    private $definitions;

    /**
     * @param \Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig
     * @param \Magento\Framework\ObjectManager\DefinitionInterface $definitions
     * @param \Magento\Developer\Model\Di\PluginList $pluginList
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @param string $className
     * @return array|null
     * @since 2.2.0
     */
    private function getConstructorParameters($className)
    {
        $parameters = $this->definitions->getParameters($className);
        return $parameters;
    }

    /**
     * Retrieve array of parameters for the class constructor
     *
     * @param string $className
     * @return array
     * @since 2.2.0
     */
    public function getParameters($className)
    {
        $result = [];
        $diConfiguration = $this->getConfiguredConstructorParameters($className);
        $originalParameters = $this->isVirtualType($className) ?
            $this->getConstructorParameters($this->getVirtualTypeBase($className)) :
            $this->getConstructorParameters($this->getPreference($className));

        if (!$originalParameters) {
            return $result;
        }

        foreach ($originalParameters as $parameter) {
            $paramArray = [$parameter[0], $parameter[1], is_array($parameter[3]) ? "<empty array>" : $parameter[3]];
            if (isset($diConfiguration[$parameter[0]])) {
                $paramArray[2] = $this->renderParameters($diConfiguration[$parameter[0]]);
            }
            $result[] = $paramArray;
        }
        return $result;
    }

    /**
     * Recursively retrieve array parameters
     *
     * @param string $configuredParameter
     * @return array|null
     * @since 2.2.0
     */
    private function renderParameters($configuredParameter)
    {
        $result = null;
        if (is_array($configuredParameter)) {
            if (isset($configuredParameter['instance'])) {
                $result = 'instance of ' . $configuredParameter['instance'];
            } else {
                foreach ($configuredParameter as $keyName => $instance) {
                    $result[$keyName] = $this->renderParameters($instance);
                }
            }
        } else {
            $result = 'string ' . $configuredParameter;
        }
        return $result;
    }

    /**
     * Retrieve configured types of parameters of the constructor for the preference of the class
     *
     * @param string $className
     * @return array|null
     * @since 2.2.0
     */
    private function getConfiguredConstructorParameters($className)
    {
        return $this->objectManagerConfig->getArguments($className);
    }

    /**
     * Retrieve virtual types for the class and the preference of the class
     *
     * @param string $className
     * @return array
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
