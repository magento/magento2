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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme helper
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Helper_Theme extends Mage_Adminhtml_Helper_Data
{
    /**
     * Color Themes Cache
     *
     * @param array|null
     */
    protected $_themeArray = null;

    /**
     * Return for Color Themes Fields array.
     *
     * @return array
     */
    public function getThemeAjaxParameters()
    {
        $themesArray = array (
            'conf_native_navigationBar_tintColor'
                => 'conf[native][navigationBar][tintColor]',
            'conf_native_body_primaryColor'
                => 'conf[native][body][primaryColor]',
            'conf_native_body_secondaryColor'
                => 'conf[native][body][secondaryColor]',
            'conf_native_categoryItem_backgroundColor'
                => 'conf[native][categoryItem][backgroundColor]',
            'conf_native_categoryItem_tintColor'
                => 'conf[native][categoryItem][tintColor]',

            'conf_extra_fontColors_header'
                => 'conf[extra][fontColors][header]',
            'conf_extra_fontColors_primary'
                => 'conf[extra][fontColors][primary]',
            'conf_extra_fontColors_secondary'
                => 'conf[extra][fontColors][secondary]',
            'conf_extra_fontColors_price'
                => 'conf[extra][fontColors][price]',

            'conf_native_body_backgroundColor'
                => 'conf[native][body][backgroundColor]',
            'conf_native_body_scrollBackgroundColor'
                => 'conf[native][body][scrollBackgroundColor]',
            'conf_native_itemActions_relatedProductBackgroundColor'
                => 'conf[native][itemActions][relatedProductBackgroundColor]'
        );
        return $themesArray;
    }

    /**
     * Returns JSON ready Themes array
     *
     * @param bool $flushCache load defaults
     * @return array
     */
    public function getAllThemesArray($flushCache = false)
    {
        $result = array();
        $themes = $this->getAllThemes($flushCache);
        foreach ($themes as $theme) {
            $result[$theme->getName()] = $theme->getFormData();
        }
        return $result;
    }

    /**
     * Get dropdown select image for theme
     *
     * @param string $themeId
     * @return string Image url
     */
    public function getThemeImageUrl($themeId)
    {
        $themeImage = array_key_exists($themeId, $this->getDefaultThemes()) ? $themeId : 'user_custom';
        return Mage::helper('Mage_XmlConnect_Helper_Image')->getSkinImagesUrl('swatch_' . $themeImage . '.gif');
    }

    /**
     * Get themes dropdown selector html
     *
     * @param string $themeId
     * @return string
     */
    public function getThemesSelector($themeId = '')
    {
        if (Mage::registry('current_app') !== null) {
            $themeId = Mage::registry('current_app')->getData('conf/extra/theme');
        }

        if (!$themeId) {
            $themeId = $this->getDefaultThemeName();
        }

        $currentTheme = $this->getThemeByName($themeId);
        if ($currentTheme === null) {
            $themeId = $this->getDefaultThemeName();
            $currentTheme = $this->getThemeByName($themeId);
        }

        if (!($currentTheme instanceof Mage_XmlConnect_Model_Theme)) {
            Mage::throwException(
                Mage::helper('Mage_XmlConnect_Helper_Data')->__('Can\'t load selected theme. Please check your media folder permissions.')
            );
        }

        $themeList = '';
        foreach ($this->getAllThemes(true) as $theme) {
            $themeList .= '<li id="' . $theme->getName() . '">';
            $themeList .= '<a rel="' . $theme->getName() . '" style="cursor:pointer;">' . $theme->getLabel();
            $themeList .= '<span>';
            $themeList .= '<img src="' . $this->getThemeImageUrl($theme->getName()) . '"/>';
            $themeList .= '</span></a></li>';
        }

        $themesDdl = <<<EOT
        <ul class="dropdown theme_selector" id="theme_selector_id">
            <li class="ddtitle theme_selector">
                <a style="cursor:pointer;">{$currentTheme->getLabel()}
                    <span>
                        <img src="{$this->getThemeImageUrl($themeId)}"/>
                    </span>
                </a>
            </li>
            <li style="display:none;" class="ddlist">
                <ul>
                {$themeList}
                </ul>
            </li>
        </ul>
EOT;
        return $themesDdl;
    }

    /**
     * Reads directory media/xmlconnect/themes/*
     *
     * @param bool $flushCache Reads default color Themes
     * @return array contains Mage_XmlConnect_Model_Theme
     */
    public function getAllThemes($flushCache = false)
    {
        if (!$this->_themeArray || $flushCache) {
            $saveLibxmlErrors   = libxml_use_internal_errors(true);
            $this->_themeArray  = array();
            $themeDir = $this->getMediaThemePath();
            $ioFile = new Varien_Io_File();
            $ioFile->checkAndCreateFolder($themeDir);
            $ioFile->open(array('path' => $themeDir));
            try {
                $fileList = $ioFile->ls(Varien_Io_File::GREP_FILES);
                if (!count($fileList)) {
                    $this->resetTheme();
                    $this->getAllThemes(true);
                }
                foreach ($fileList as $file) {
                    $src = $themeDir . DS . $file['text'];
                    if (is_readable($src)) {
                        $theme = Mage::getModel('Mage_XmlConnect_Model_Theme', array('file' => $src));
                        $this->_themeArray[$theme->getName()] = $theme;
                    }
                }
                asort($this->_themeArray);
                libxml_use_internal_errors($saveLibxmlErrors);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this->_themeArray;
    }

    /**
     * Reads default theme directory
     *
     * @throws Mage_Core_Exception
     * @return array contains Mage_XmlConnect_Model_Theme
     */
    public function getDefaultThemes()
    {
        $saveLibxmlErrors   = libxml_use_internal_errors(true);
        $defaultThemeArray  = array();
        $themeDir = $this->_getDefaultThemePath();
        $ioFile = new Varien_Io_File();
        $ioFile->open(array('path' => $themeDir));
        try {
            $fileList = $ioFile->ls(Varien_Io_File::GREP_FILES);
            foreach ($fileList as $file) {
                $src = $themeDir . DS . $file['text'];
                if (is_readable($src)) {
                    $theme = Mage::getModel('Mage_XmlConnect_Model_Theme', array('file' => $src));
                    $defaultThemeArray[$theme->getName()] = $theme;
                }
            }
            libxml_use_internal_errors($saveLibxmlErrors);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        if (!count($defaultThemeArray)) {
            Mage::throwException(Mage::helper('Mage_XmlConnect_Helper_Data')->__('Can\'t load default themes.'));
        }
        return $defaultThemeArray;
    }

    /**
     * Create new custom theme
     *
     * @param  $themeName string
     * @param  $data array
     * @return Mage_XmlConnect_Model_Theme
     */
    public function createNewTheme($themeName, $data)
    {
        /** @var $defaultTheme Mage_XmlConnect_Model_Theme */
        $defaultTheme = $this->getThemeByName($this->getDefaultThemeName());
        return $defaultTheme->createNewTheme($themeName, $data);
    }

    /**
     * Get default theme path: /xmlconnect/etc/themes/*
     *
     * @return string
     */
    protected function _getDefaultThemePath()
    {
        return Mage::getModuleDir('etc', 'Mage_XmlConnect') . DS . 'themes';
    }

    /**
     * Get media theme path: media/xmlconnect/themes/*
     *
     * @return string
     */
    public function getMediaThemePath()
    {
        return Mage::getBaseDir('media') . DS . 'xmlconnect' . DS . 'themes';
    }

    /**
     * Reset themes color changes
     * Copy /xmlconnect/etc/themes/* to media/xmlconnect/themes/*
     *
     * @throws Mage_Core_Exception
     * @param null $theme
     * @return null
     */
    public function resetTheme($theme = null)
    {
        $themeDir = $this->getMediaThemePath();
        $defaultThemeDir = $this->_getDefaultThemePath();

        $ioFile = new Varien_Io_File();
        $ioFile->open(array('path' => $defaultThemeDir));
        $fileList = $ioFile->ls(Varien_Io_File::GREP_FILES);
        foreach ($fileList as $file) {
            $f = $file['text'];
            $src = $defaultThemeDir . DS . $f;
            $dst = $themeDir . DS .$f;

            if ($theme && ($theme . '.xml') != $f) {
                continue;
            }

            if (!$ioFile->cp($src, $dst)) {
                Mage::throwException(Mage::helper('Mage_XmlConnect_Helper_Data')->__('Can\'t copy file "%s" to "%s".', $src, $dst));
            } else {
                $ioFile->chmod($dst, 0755);
            }
        }
    }

    /**
     * Get theme object by name
     *
     * @param string $name
     * @return Mage_XmlConnect_Model_Theme|null
     */
    public function getThemeByName($name)
    {
        $themes = $this->getAllThemes();
        $theme = isset($themes[$name]) ? $themes[$name] : null;
        return $theme;
    }

    /**
     * Return predefined custom theme name
     *
     * @return string
     */
    public function getCustomThemeName()
    {
        return 'custom';
    }

    /**
     * Return predefined default theme name
     *
     * @return string
     */
    public function getDefaultThemeName()
    {
        return 'default';
    }

    /**
     * Get current theme name
     *
     * @return string
     */
    public function getThemeId()
    {
        $themeId = Mage::helper('Mage_XmlConnect_Helper_Data')->getApplication()->getData('conf/extra/theme');

        if ($this->getThemeByName($themeId) === null) {
            $themeId = null;
        }

        if (empty($themeId)) {
            $themeId = $this->getDefaultThemeName();
        }
        return $themeId;
    }

    /**
     * Get theme label by theme name
     *
     * @param array $themes
     * @param bool $themeId
     * @return string
     */
    public function getThemeLabel(array $themes, $themeId = false)
    {
        $themeLabel = '';
        $themeId    = $themeId ? $themeId : $this->getThemeId();

        foreach ($themes as $theme) {
            if ($theme->getName() == $themeId) {
                $themeLabel = $theme->getLabel();
                break;
            }
        }
        return $themeLabel;
    }

    /**
     * Delete theme by id
     *
     * @param  $themeId
     * @return bool
     */
    public function deleteTheme($themeId)
    {
        $result = false;
        $ioFile = new Varien_Io_File();
        $ioFile->cd($this->getMediaThemePath());
        $themeFile = basename($themeId . '.xml');
        if ($ioFile->fileExists($themeFile)) {
            $result = $ioFile->rm($themeFile);
        }
        return $result;
    }
}
