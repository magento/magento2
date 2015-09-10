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
     * Themes
     *
     * @var Package[]
     */
    private $themes = [];

    /**
     * Constructor
     *
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(ComponentRegistrarInterface $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::THEME) as $key => $path) {
            $this->themes[$key] = new Package($key, $path);
        }
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
        if (!isset($this->themes[$key])) {
            throw new \Exception("No theme registered for '$key'");
        }
        return $this->themes[$key];
    }

    /**
     * Get all themes
     *
     * @return Package[]
     */
    public function getThemes()
    {
        return $this->themes;
    }
}
