<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * Value-object for files found in components
 * @since 2.0.0
 */
class ComponentFile
{
    /**
     * Component type
     *
     * @var string
     * @since 2.0.0
     */
    private $componentType;

    /**
     * Component name
     *
     * @var string
     * @since 2.0.0
     */
    private $componentName;

    /**
     * Full path
     *
     * @var string
     * @since 2.0.0
     */
    private $path;

    /**
     * Constructor
     *
     * @param string $componentType
     * @param string $componentName
     * @param string $fullPath
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getComponentType()
    {
        return $this->componentType;
    }

    /**
     * Get component name
     *
     * @return string
     * @since 2.0.0
     */
    public function getComponentName()
    {
        return $this->componentName;
    }

    /**
     * Get full path to the component
     *
     * @return string
     * @since 2.0.0
     */
    public function getFullPath()
    {
        return $this->path;
    }
}
