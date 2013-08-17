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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme Customization Path
 */
class Mage_Core_Model_Theme_Customization_Path
{
    /**
     * Customization directory name
     */
    const DIR_NAME = 'theme_customization';

    /**
     * @var Mage_Core_Model_Dir
     */
    protected $_dir;

    /**
     * Initialize dependencies
     *
     * @param Mage_Core_Model_Dir $dir
     */
    public function __construct(Mage_Core_Model_Dir $dir)
    {
        $this->_dir = $dir;
    }

    /**
     * Returns customization absolute path
     *
     * @param Mage_Core_Model_Theme $theme
     * @return string|null
     */
    public function getCustomizationPath(Mage_Core_Model_Theme $theme)
    {
        $path = null;
        if ($theme->getId()) {
            $path = $this->_dir->getDir(Mage_Core_Model_Dir::MEDIA)
                . DIRECTORY_SEPARATOR . self::DIR_NAME
                . DIRECTORY_SEPARATOR . $theme->getId();
        }
        return $path;
    }

    /**
     * Get directory where themes files are stored
     *
     * @param Mage_Core_Model_Theme $theme
     * @return string|null
     */
    public function getThemeFilesPath(Mage_Core_Model_Theme $theme)
    {
        $path = null;
        if ($theme->getFullPath()) {
            $physicalThemesDir = $this->_dir->getDir(Mage_Core_Model_Dir::THEMES);
            $path = Magento_Filesystem::fixSeparator($physicalThemesDir . DIRECTORY_SEPARATOR . $theme->getFullPath());
        }
        return $path;
    }

    /**
     * Get path to custom view configuration file
     *
     * @param Mage_Core_Model_Theme $theme
     * @return string|null
     */
    public function getCustomViewConfigPath(Mage_Core_Model_Theme $theme)
    {
        $path = null;
        if ($theme->getId()) {
            $path = $this->getCustomizationPath($theme) . DIRECTORY_SEPARATOR
                . Mage_Core_Model_Theme::FILENAME_VIEW_CONFIG;
        }
        return $path;
    }
}
