<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

use Magento\Framework\Config\Theme;

/**
 * Value-object for a theme package
 * @since 2.0.0
 */
class ThemePackage
{
    /**
     * Area
     *
     * @var string
     * @since 2.0.0
     */
    private $area;

    /**
     * Vendor name
     *
     * @var string
     * @since 2.0.0
     */
    private $vendor;

    /**
     * Theme name
     *
     * @var string
     * @since 2.0.0
     */
    private $name;

    /**
     * Theme path key
     *
     * @var string
     * @since 2.0.0
     */
    private $key;

    /**
     * Full path to the theme
     *
     * @var string
     * @since 2.0.0
     */
    private $path;

    /**
     * Constructor
     *
     * @param string $key
     * @param string $path
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Get vendor name
     *
     * @return string
     * @since 2.0.0
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * Get theme name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get path key
     *
     * @return string
     * @since 2.0.0
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get full path
     *
     * @return string
     * @since 2.0.0
     */
    public function getPath()
    {
        return $this->path;
    }
}
