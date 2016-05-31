<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

use Magento\Framework\Config\Theme;

/**
 * Value-object for a theme package
 */
class ThemePackage
{
    /**
     * Area
     *
     * @var string
     */
    private $area;

    /**
     * Vendor name
     *
     * @var string
     */
    private $vendor;

    /**
     * Theme name
     *
     * @var string
     */
    private $name;

    /**
     * Theme path key
     *
     * @var string
     */
    private $key;

    /**
     * Full path to the theme
     *
     * @var string
     */
    private $path;

    /**
     * Constructor
     *
     * @param string $key
     * @param string $path
     */
    public function __construct($key, $path)
    {
        $keyParts = explode(Theme::THEME_PATH_SEPARATOR, $key);
        if (count($keyParts) != 3) {
            throw new \UnexpectedValueException(
                "Theme's key does not correspond to required format: '<area>/<vendor>/<name>'"
            );
        }

        $this->key = $key;
        $this->path = $path;
        $this->area = $keyParts[0];
        $this->vendor = $keyParts[1];
        $this->name = $keyParts[2];
    }

    /**
     * Get area
     *
     * @return string
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Get vendor name
     *
     * @return string
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * Get theme name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get path key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get full path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
