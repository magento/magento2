<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\Di;

class Information
{
    /**
     * @var \Magento\Framework\ObjectManager\ConfigInterface
     */
    private $objectManagerConfig;

    /**
     * @var string[]
     */
    private $preferences = [];

    /**
     * @param \Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig
     */
    public function __construct(\Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig)
    {
        $this->objectManagerConfig = $objectManagerConfig;
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
}