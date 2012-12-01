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
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * A theme selector for design editor frontend toolbar panel
 */
class Mage_DesignEditor_Block_Toolbar_Theme extends Mage_Core_Block_Template
{
    /**
     * Html id of the theme select control
     */
    const VDE_HTML_THEME_ID = 'visual_design_editor_theme';

    /**
     * Get current theme
     *
     * @return Mage_Core_Model_Theme
     */
    public function getTheme()
    {
        return Mage::registry('vde_theme');
    }

    /**
     * Returns whether theme selected in current store design
     *
     * @param int|string $theme
     * @return bool
     */
    public function isThemeSelected($theme)
    {
        $currentTheme = Mage::getDesign()->getDesignTheme()->getId();
        return $currentTheme == $theme;
    }

    /**
     * Returns html id of the theme select control
     *
     * @return string
     */
    public function getSelectHtmlId()
    {
        return self::VDE_HTML_THEME_ID;
    }
}
