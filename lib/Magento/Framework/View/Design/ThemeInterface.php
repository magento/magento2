<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Design;

/**
 * Interface ThemeInterface
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
