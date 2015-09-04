<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * Provides ability to statically register components.
 *
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
abstract class AbstractComponentRegistrar implements ComponentRegistrarInterface
{
    /**
     * Paths to components
     *
     * @var string[]
     */
    protected $componentPaths;

    /**
     * Sets the location of a component.
     *
     * @param string $componentName Fully-qualified component name
     * @param string $path Absolute file path to the component
     * @throws \LogicException
     * @return void
     */
    public function register($componentName, $path)
    {
        if (isset($this->componentPaths[$componentName])) {
            throw new \LogicException($componentName . ' already exists');
        } else {
            $this->componentPaths[$componentName] = $path;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths()
    {
        return $this->componentPaths;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($componentName)
    {
        return isset($this->componentPaths[$componentName]) ? $this->componentPaths[$componentName] : null;
    }
}
