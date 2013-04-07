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
 * Theme data helper
 */
class Mage_Core_Helper_Theme extends Mage_Core_Helper_Abstract
{
    /**
     * XPath selector to get CSS files from layout added for HEAD block directly
     */
    const XPATH_SELECTOR_BLOCKS =
        '//block[@type="Mage_Page_Block_Html_Head"]/action[@method="addCss" or @method="addCssIe"]/*[1]';

    /**
     * XPath selector to get CSS files from layout added for HEAD block using reference
     */
    const XPATH_SELECTOR_REFS =
        '//reference[@name="head"]/action[@method="addCss" or @method="addCssIe"]/*[1]';

    /**
     * Design model
     *
     * @var Mage_Core_Model_Design_Package
     */
    protected $_design;

    /**
     * Directories
     *
     * @var Mage_Core_Model_Dir
     */
    protected $_dirs;

    /**
     * Layout merge factory
     *
     * @var Mage_Core_Model_Layout_Merge_Factory
     */
    protected $_layoutMergeFactory;

    /**
     * Theme collection model
     *
     * @var Mage_Core_Model_Resource_Theme_Collection
     */
    protected $_themeCollection;

    /**
     * @var Mage_Core_Model_Theme_Factory
     */
    protected $_themeFactory;

    /**
     * @param Mage_Core_Helper_Context $context
     * @param Mage_Core_Model_Design_Package $design
     * @param Mage_Core_Model_Dir $dirs
     * @param Mage_Core_Model_Layout_Merge_Factory $layoutMergeFactory
     * @param Mage_Core_Model_Resource_Theme_Collection $themeCollection
     * @param Mage_Core_Model_Theme_Factory $themeFactory
     */
    public function __construct(
        Mage_Core_Helper_Context $context,
        Mage_Core_Model_Design_Package $design,
        Mage_Core_Model_Dir $dirs,
        Mage_Core_Model_Layout_Merge_Factory $layoutMergeFactory,
        Mage_Core_Model_Resource_Theme_Collection $themeCollection,
        Mage_Core_Model_Theme_Factory $themeFactory
    ) {
        $this->_design = $design;
        $this->_dirs = $dirs;
        $this->_layoutMergeFactory = $layoutMergeFactory;
        $this->_themeCollection = $themeCollection;
        $this->_themeFactory = $themeFactory;
        parent::__construct($context);
    }

    /**
     * Get CSS files of a given theme
     *
     * Returned array has a structure
     * array(
     *   'Mage_Catalog::widgets.css' => 'http://mage2.com/pub/static/frontend/_theme15/en_US/Mage_Cms/widgets.css'
     * )
     *
     * @param Mage_Core_Model_Theme $theme
     * @return array
     */
    public function getCssFiles($theme)
    {
        $arguments = array(
            'area'  => $theme->getArea(),
            'theme' => $theme->getThemeId()
        );
        /** @var $layoutMerge Mage_Core_Model_Layout_Merge */
        $layoutMerge = $this->_layoutMergeFactory->create(array('arguments' => $arguments));
        $layoutElement = $layoutMerge->getFileLayoutUpdatesXml();

        $elements = array_merge(
            $layoutElement->xpath(self::XPATH_SELECTOR_REFS) ?: array(),
            $layoutElement->xpath(self::XPATH_SELECTOR_BLOCKS) ?: array()
        );

        $params = array(
            'area'       => $theme->getArea(),
            'themeModel' => $theme,
            'skipProxy'  => true
        );

        $basePath = $this->_dirs->getDir(Mage_Core_Model_Dir::ROOT);
        $files = array();
        foreach ($elements as $fileId) {
            $fileId = (string)$fileId;
            $path = $this->_design->getViewFile($fileId, $params);
            $file = array(
                'id'       => $fileId,
                'path'     => Magento_Filesystem::fixSeparator($path),
             );
            $file['safePath'] = $this->getSafePath($file['path'], $basePath);

            //keys are used also to remove duplicates
            $files[$fileId] = $file;
        }

        return $files;
    }

    /**
     * Get CSS files by group
     *
     * @param Mage_Core_Model_Theme $theme
     * @return array
     * @throws LogicException
     */
    public function getGroupedCssFiles($theme)
    {
        $jsDir = Magento_Filesystem::fixSeparator($this->_dirs->getDir(Mage_Core_Model_Dir::PUB_LIB));
        $codeDir = Magento_Filesystem::fixSeparator($this->_dirs->getDir(Mage_Core_Model_Dir::MODULES));
        $designDir = Magento_Filesystem::fixSeparator($this->_dirs->getDir(Mage_Core_Model_Dir::THEMES));

        $groups = array();
        $themes = array();
        foreach ($this->getCssFiles($theme) as $file) {
            $this->_detectTheme($file, $designDir);
            $this->_detectGroup($file, $designDir, $jsDir, $codeDir);

            if (isset($file['theme']) && $file['theme']->getThemeId()) {
                $themes[$file['theme']->getThemeId()] = $file['theme'];
            }

            if (!isset($file['group'])) {
                throw new LogicException($this->__('Group is missed for file "%s"', $file['safePath']));
            }
            $group = $file['group'];
            unset($file['theme']);
            unset($file['group']);

            if (!isset($groups[$group])) {
                $groups[$group] = array();
            }
            $groups[$group][] = $file;
        }

        if (count($themes) > 1) {
            $themes = $this->_sortThemesByHierarchy($themes);
        }

        $order = array_merge(array($codeDir, $jsDir), array_map(function ($fileTheme) {
            /** @var $fileTheme Mage_Core_Model_Theme */
            return $fileTheme->getThemeId();
        }, $themes));
        $groups = $this->_sortArrayByArray($groups, $order);

        $labels = $this->_getGroupLabels($themes, $jsDir, $codeDir);
        foreach ($groups as $key => $group) {
            usort($group, array($this, '_sortGroupFilesCallback'));
            $groups[$labels[$key]] = $group;
            unset($groups[$key]);
        }
        return $groups;
    }

    /**
     * Detect theme view file belongs to and set it to file data under "theme" key
     *
     * @param array $file
     * @param string $designDir
     * @return Mage_Core_Helper_Theme
     * @throws LogicException
     */
    protected function _detectTheme(&$file, $designDir)
    {
        //TODO use cache here, so files of the same theme share one model

        $isInsideDesignDir = substr($file['path'], 0, strlen($designDir)) == $designDir;
        if (!$isInsideDesignDir) {
            return $this;
        }

        $relativePath = substr($file['path'], strlen($designDir));

        $area = strtok($relativePath, Magento_Filesystem::DIRECTORY_SEPARATOR);
        $package = strtok(Magento_Filesystem::DIRECTORY_SEPARATOR);
        $theme = strtok(Magento_Filesystem::DIRECTORY_SEPARATOR);

        if ($area === false || $package === false || $theme === false) {
            throw new LogicException($this->__('Theme path "%s/%s/%s" is incorrect', $area, $package, $theme));
        }
        $themeModel = $this->_themeCollection->getThemeByFullPath($area . '/' . $package . '/' . $theme);

        if (!$themeModel || !$themeModel->getThemeId()) {
            throw new LogicException(
                $this->__('Invalid theme loaded by theme path "%s/%s/%s"', $area, $package, $theme)
            );
        }

        $file['theme'] = $themeModel;

        return $this;
    }

    /**
     * Detect group where file should be placed and set it to file data under "group" key
     *
     * @param array $file
     * @param string $designDir
     * @param string $jsDir
     * @param string $codeDir
     * @return Mage_Core_Helper_Theme
     * @throws LogicException
     */
    protected function _detectGroup(&$file, $designDir, $jsDir, $codeDir)
    {
        $group = null;
        if (substr($file['path'], 0, strlen($designDir)) == $designDir) {
            if (!isset($file['theme']) || !$file['theme']->getThemeId()) {
                throw new LogicException($this->__('Theme is missed for file "%s"', $file['safePath']));
            }
            $group = $file['theme']->getThemeId();
        } elseif (substr($file['path'], 0, strlen($jsDir)) == $jsDir) {
            $group = $jsDir;
        } elseif (substr($file['path'], 0, strlen($codeDir)) == $codeDir) {
            $group = $codeDir;
        } else {
            throw new LogicException($this->__('Invalid view file directory "%s"', $file['safePath']));
        }
        $file['group'] = $group;

        return $this;
    }

    /**
     * Sort themes according to their hierarchy
     *
     * @param array $themes
     * @return array
     */
    protected function _sortThemesByHierarchy(array $themes)
    {
        uasort($themes, array($this, '_sortThemesByHierarchyCallback'));
        return $themes;
    }

    /**
     * Sort one associative array according to another array
     *
     * $groups = array(
     *     b => item2,
     *     a => item1,
     *     c => item3,
     * );
     * $order = array(a,b,c);
     * result: array(
     *     a => item1,
     *     b => item2,
     *     c => item3,
     * )
     *
     * @param array $groups
     * @param array $order
     * @return array
     */
    protected function _sortArrayByArray(array $groups, array $order)
    {
        $ordered = array();
        foreach ($order as $key) {
            if (array_key_exists($key, $groups)) {
                $ordered[$key] = $groups[$key];
                unset($groups[$key]);
            }
        }
        return $ordered + $groups;
    }

    /**
     * Get group labels
     *
     * @param array $themes
     * @param string $jsDir
     * @param string $codeDir
     * @return array
     */
    protected function _getGroupLabels(array $themes, $jsDir, $codeDir)
    {
        $labels = array(
            $jsDir => $this->__('Library files'),
            $codeDir => $this->__('Framework files')
        );
        foreach ($themes as $theme) {
            /** @var $theme Mage_Core_Model_Theme */
            $labels[$theme->getThemeId()] = $this->__('"%s" Theme files', $theme->getThemeTitle());
        }
        return $labels;
    }

    /**
     * Callback for sorting files inside group
     *
     * Return "1" if $firstFile should go before $secondFile, otherwise return "-1"
     *
     * @param array $firstFile
     * @param array $secondFile
     * @return int
     */
    protected function _sortGroupFilesCallback(array $firstFile, array $secondFile)
    {
        $hasContextFirst = strpos($firstFile['id'], '::') !== false;
        $hasContextSecond = strpos($secondFile['id'], '::') !== false;

        if ($hasContextFirst && $hasContextSecond) {
            $result = strcmp($firstFile['id'], $secondFile['id']);
        } elseif (!$hasContextFirst && !$hasContextSecond) {
            $result = strcmp($firstFile['id'], $secondFile['id']);
        } elseif ($hasContextFirst) {
            //case when first item has module context and second item doesn't
            $result = 1;
        } else {
            //case when second item has module context and first item doesn't
            $result = -1;
        }
        return $result;
    }

    /**
     * Sort themes by hierarchy callback
     *
     * @param Mage_Core_Model_Theme $firstTheme
     * @param Mage_Core_Model_Theme $secondTheme
     * @return int
     */
    protected function _sortThemesByHierarchyCallback($firstTheme, $secondTheme)
    {
        $parentTheme = $firstTheme->getParentTheme();
        while ($parentTheme) {
            if ($parentTheme->getThemeId() == $secondTheme->getThemeId()) {
                return -1;
            }
            $parentTheme = $parentTheme->getParentTheme();
        }
        return 1;
    }

    /**
     * Get relative file path cut to be safe for public sharing
     *
     * Path is considered from the base Magento directory
     *
     * @param string $filePath
     * @param string $basePath
     * @return string
     */
    public function getSafePath($filePath, $basePath)
    {
        return ltrim(str_ireplace($basePath, '', $filePath), '\\/');
    }

    /**
     * Load theme by theme id
     * Method also checks if theme actually loaded and if theme is editable
     *
     * @param int $themeId
     * @return Mage_Core_Model_Theme
     * @throws Mage_Core_Exception
     */
    public function loadEditableTheme($themeId)
    {
        $theme = $this->_loadTheme($themeId);
        if (!$theme->isEditable()) {
            throw new Mage_Core_Exception($this->__('Theme "%s" is not editable.', $themeId));
        }
        return $theme;
    }

    /**
     * Load theme by theme id
     * Method also checks if theme actually loaded and if theme is visible
     *
     * @param int $themeId
     * @return Mage_Core_Model_Theme
     * @throws Mage_Core_Exception
     */
    public function loadVisibleTheme($themeId)
    {
        $theme = $this->_loadTheme($themeId);
        if (!$theme->isVisible()) {
            throw new Mage_Core_Exception($this->__('Theme "%s" is not visible.', $themeId));
        }
        return $theme;
    }

    /**
     * Load theme by theme id and checks if theme actually loaded
     *
     * @param $themeId
     * @return Mage_Core_Model_Theme
     * @throws Mage_Core_Exception
     */
    protected function _loadTheme($themeId)
    {
        $theme = $this->_themeFactory->create();
        if (!($themeId && $theme->load($themeId)->getId())) {
            throw new Mage_Core_Exception($this->__('Theme "%s" was not found.', $themeId));
        }
        return $theme;
    }
}
