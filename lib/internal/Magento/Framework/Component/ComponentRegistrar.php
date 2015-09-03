<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * Provides ability to statically register components
 *
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
abstract class ComponentRegistrar implements ComponentRegistrarInterface
{
    /**
     * Paths to components
     *
     * @var string[]
     */
    private static $componentPaths = [];

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
        if (isset(self::$componentPaths[$componentName])) {
            throw new \LogicException ($componentName . ' already exists');
        } else {
            self::$componentPaths[$componentName] = $path;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths()
    {
        return self::$componentPaths;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($componentName)
    {
        return isset(self::$componentPaths[$componentName]) ? self::$componentPaths[$componentName] : null;
    }
}
