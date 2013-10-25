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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme Customization Path
 */
namespace Magento\Core\Model\Theme\Customization;

class Path
{
    /**
     * Customization directory name
     */
    const DIR_NAME = 'theme_customization';

    /**
     * @var \Magento\App\Dir
     */
    protected $_dir;

    /**
     * Initialize dependencies
     *
     * @param \Magento\App\Dir $dir
     */
    public function __construct(\Magento\App\Dir $dir)
    {
        $this->_dir = $dir;
    }

    /**
     * Returns customization absolute path
     *
     * @param \Magento\View\Design\ThemeInterface $theme
     * @return string|null
     */
    public function getCustomizationPath(\Magento\View\Design\ThemeInterface $theme)
    {
        $path = null;
        if ($theme->getId()) {
            $path = $this->_dir->getDir(\Magento\App\Dir::MEDIA)
                . DIRECTORY_SEPARATOR . self::DIR_NAME
                . DIRECTORY_SEPARATOR . $theme->getId();
        }
        return $path;
    }

    /**
     * Get directory where themes files are stored
     *
     * @param \Magento\View\Design\ThemeInterface $theme
     * @return string|null
     */
    public function getThemeFilesPath(\Magento\View\Design\ThemeInterface $theme)
    {
        $path = null;
        if ($theme->getFullPath()) {
            $physicalThemesDir = $this->_dir->getDir(\Magento\App\Dir::THEMES);
            $path = \Magento\Filesystem::fixSeparator($physicalThemesDir . DIRECTORY_SEPARATOR . $theme->getFullPath());
        }
        return $path;
    }

    /**
     * Get path to custom view configuration file
     *
     * @param \Magento\View\Design\ThemeInterface $theme
     * @return string|null
     */
    public function getCustomViewConfigPath(\Magento\View\Design\ThemeInterface $theme)
    {
        $path = null;
        if ($theme->getId()) {
            $path = $this->getCustomizationPath($theme) . DIRECTORY_SEPARATOR
                . \Magento\Core\Model\Theme::FILENAME_VIEW_CONFIG;
        }
        return $path;
    }
}
