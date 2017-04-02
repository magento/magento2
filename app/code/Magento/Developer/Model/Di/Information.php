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
    public function getConstructorParameters($className)
    {
        $preferenceClass = $this->getPreference($className);
        $parameters = $this->definitions->getParameters($preferenceClass);
        return $parameters;
    }

    /**
     * Retrieve configured types of parameters of the constructor for the preference of the class
     *
     * @param $className
     * @return array|null
     */
    public function getConfiguredConstructorParameters($className)
    {
        $preferenceClass = $this->getPreference($className);
        return $this->objectManagerConfig->getArguments($preferenceClass);
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
        $virtualTypes = [];
        foreach ($this->objectManagerConfig->getVirtualTypes() as $virtualType => $baseName) {
            if ($baseName == $className || $baseName == $preference) {
                $virtualTypes[] = $virtualType;
            }
        }
        return $virtualTypes;
    }

    /**
     * @param $className
     * @return array
     */
    public function getPlugins($className)
    {
        return $this->pluginList->getPluginsListByClass($className);

    }
}