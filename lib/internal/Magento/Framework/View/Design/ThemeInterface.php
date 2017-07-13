<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design;

/**
 * Interface ThemeInterface
 *
 * @api
 */
interface ThemeInterface
{
    /**
     * Separator between theme_path elements
     */
    const PATH_SEPARATOR = '/';

    /**
     * Separator between parts of full theme code (package and theme code)
     */
    const CODE_SEPARATOR = '/';

    /**
     * Physical theme type
     */
    const TYPE_PHYSICAL = 0;

    /**
     * Virtual theme type
     */
    const TYPE_VIRTUAL = 1;

    /**
     * Staging theme type
     */
    const TYPE_STAGING = 2;

    /**
     * Retrieve code of an area a theme belongs to
     *
     * @return string
     */
    public function getArea();

    /**
     * Retrieve theme path unique within an area
     *
     * @return string
     */
    public function getThemePath();

    /**
     * Retrieve theme path unique across areas
     *
     * @return string
     */
    public function getFullPath();

    /**
     * Retrieve parent theme instance
     *
     * @return ThemeInterface|null
     */
    public function getParentTheme();

    /**
     * Get code of the theme
     *
     * @return string
     */
    public function getCode();

    /**
     * Check if theme is physical
     *
     * @return bool
     */
    public function isPhysical();

    /**
     * Return the full theme inheritance sequence, from the root theme till a specified one
     * Format: array([<root_theme>, ..., <parent_theme>,] <current_theme>)
     *
     * @return ThemeInterface[]
     */
    public function getInheritedThemes();

    /**
     * Get theme id
     *
     * @return int
     */
    public function getId();
}
