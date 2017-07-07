<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * Value-object for files found in components
 */
class ComponentFile
{
    /**
     * Component type
     *
     * @var string
     */
    private $componentType;

    /**
     * Component name
     *
     * @var string
     */
    private $componentName;

    /**
     * Full path
     *
     * @var string
     */
    private $path;

    /**
     * Constructor
     *
     * @param string $componentType
     * @param string $componentName
     * @param string $fullPath
     */
    public function __construct($componentType, $componentName, $fullPath)
    {
        $this->componentType = $componentType;
        $this->componentName = $componentName;
        $this->path = $fullPath;
    }

    /**
     * Get component type
     *
     * @return string
     */
    public function getComponentType()
    {
        return $this->componentType;
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return $this->componentName;
    }

    /**
     * Get full path to the component
     *
     * @return string
     */
    public function getFullPath()
    {
        return $this->path;
    }
}
