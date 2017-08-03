<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;

/**
 * List of theme package value objects
 * @since 2.0.0
 */
class ThemePackageList
{
    /**
     * Component registrar
     *
     * @var ComponentRegistrarInterface
     * @since 2.0.0
     */
    private $componentRegistrar;

    /**
     * Factory for ThemePackage
     *
     * @var ThemePackageFactory
     * @since 2.0.0
     */
    private $factory;

    /**
     * Constructor
     *
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ThemePackageFactory $factory
     * @since 2.0.0
     */
    public function __construct(ComponentRegistrarInterface $componentRegistrar, ThemePackageFactory $factory)
    {
        $this->componentRegistrar = $componentRegistrar;
        $this->factory = $factory;
    }

    /**
     * Get theme by path key
     *
     * @param string $key
     * @return ThemePackage
     * @throws \UnexpectedValueException
     * @since 2.0.0
     */
    public function getTheme($key)
    {
        $themePath = $this->componentRegistrar->getPath(ComponentRegistrar::THEME, $key);
        if (empty($themePath)) {
            throw new \UnexpectedValueException("No theme registered with name '$key'");
        }
        return $this->factory->create($key, $themePath);
    }

    /**
     * Get all themes
     *
     * @return ThemePackage[]
     * @since 2.0.0
     */
    public function getThemes()
    {
        $themes = [];
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::THEME) as $key => $path) {
            $themes[$key] = $this->factory->create($key, $path);
        }
        return $themes;
    }
}
