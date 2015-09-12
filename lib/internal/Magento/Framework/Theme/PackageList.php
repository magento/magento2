<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Theme;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;

/**
 * List of theme package value objects
 */
class PackageList
{
    /**
     * Component registrar
     *
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * Constructor
     *
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(ComponentRegistrarInterface $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Get theme by path key
     *
     * @param string $key
     * @return Package
     * @throws \Exception
     */
    public function getTheme($key)
    {
        $themePath = $this->componentRegistrar->getPath(ComponentRegistrar::THEME, $key);
        if (empty($themePath)) {
            throw new \Exception("No theme registered for '$key'");
        }
        return new Package($key, $themePath);
    }

    /**
     * Get all themes
     *
     * @return Package[]
     */
    public function getThemes()
    {
        $themes = [];
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::THEME) as $key => $path) {
            $themes[$key] = new Package($key, $path);
        }
        return $themes;
    }
}
