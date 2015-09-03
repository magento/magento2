<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * Provides ability to statically register components.
 * All classes extending this class should have a protected variable $componentPaths
 *
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
abstract class ComponentRegistrar implements ComponentRegistrarInterface
{
    /**
     * Sets the location of a component.
     *
     * @param string $componentName Fully-qualified component name
     * @param string $path Absolute file path to the component
     * @throws \LogicException
     * @return void
     */
    public static function register($componentName, $path)
    {
        if (isset(static::$componentPaths[$componentName])) {
            throw new \LogicException ($componentName . ' already exists');
        } else {
            static::$componentPaths[$componentName] = $path;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths()
    {
        return static::$componentPaths;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($componentName)
    {
        return isset(static::$componentPaths[$componentName]) ? static::$componentPaths[$componentName] : null;
    }
}
