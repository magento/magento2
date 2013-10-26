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
 * Theme registration model class
 */
namespace Magento\Core\Model\Theme;

class Registration
{
    /**
     * @var \Magento\Core\Model\Resource\Theme\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Collection of themes in file-system
     *
     * @var \Magento\Core\Model\Theme\Collection
     */
    protected $_themeCollection;

    /**
     * Allowed sequence relation by type, array(parent theme, child theme)
     *
     * @var array
     */
    protected $_allowedRelations = array(
        array(\Magento\Core\Model\Theme::TYPE_PHYSICAL, \Magento\Core\Model\Theme::TYPE_VIRTUAL),
        array(\Magento\Core\Model\Theme::TYPE_VIRTUAL, \Magento\Core\Model\Theme::TYPE_STAGING)
    );

    /**
     * Forbidden sequence relation by type
     *
     * @var array
     */
    protected $_forbiddenRelations = array(
        array(\Magento\Core\Model\Theme::TYPE_VIRTUAL, \Magento\Core\Model\Theme::TYPE_VIRTUAL),
        array(\Magento\Core\Model\Theme::TYPE_PHYSICAL, \Magento\Core\Model\Theme::TYPE_STAGING)
    );

    /**
     * Initialize dependencies
     *
     * @param \Magento\Core\Model\Resource\Theme\CollectionFactory $collectionFactory
     * @param \Magento\Core\Model\Theme\Collection $filesystemCollection
     */
    public function __construct(
        \Magento\Core\Model\Resource\Theme\CollectionFactory $collectionFactory,
        \Magento\Core\Model\Theme\Collection $filesystemCollection
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_themeCollection = $filesystemCollection;
    }

    /**
     * Theme registration
     *
     * @param string $baseDir
     * @param string $pathPattern
     * @return \Magento\Core\Model\Theme
     */
    public function register($baseDir = '', $pathPattern = '')
    {
        if (!empty($baseDir)) {
            $this->_themeCollection->setBaseDir($baseDir);
        }

        if (empty($pathPattern)) {
            $this->_themeCollection->addDefaultPattern('*');
        } else {
            $this->_themeCollection->addTargetPattern($pathPattern);
        }

        foreach ($this->_themeCollection as $theme) {
            $this->_registerThemeRecursively($theme);
        }

        $this->checkPhysicalThemes()->checkAllowedThemeRelations();

        return $this;
    }

    /**
     * Register theme and recursively all its ascendants
     * Second param is optional and is used to prevent circular references in inheritance chain
     *
     * @param \Magento\View\Design\ThemeInterface $theme
     * @param array $inheritanceChain
     * @return \Magento\Core\Model\Theme\Collection
     * @throws \Magento\Core\Exception
     */
    protected function _registerThemeRecursively(&$theme, $inheritanceChain = array())
    {
        if ($theme->getId()) {
            return $this;
        }
        $themeModel = $this->getThemeFromDb($theme->getFullPath());
        if ($themeModel->getId()) {
            $theme = $themeModel;
            return $this;
        }

        $tempId = $theme->getFullPath();
        if (in_array($tempId, $inheritanceChain)) {
            throw new \Magento\Core\Exception(__('Circular-reference in theme inheritance detected for "%1"', $tempId));
        }
        array_push($inheritanceChain, $tempId);
        $parentTheme = $theme->getParentTheme();
        if ($parentTheme) {
            $this->_registerThemeRecursively($parentTheme, $inheritanceChain);
            $theme->setParentId($parentTheme->getId());
        }

        $this->_savePreviewImage($theme);
        $theme->setType(\Magento\Core\Model\Theme::TYPE_PHYSICAL);
        $theme->save();

        return $this;
    }

    /**
     * Save preview image for theme
     *
     * @param \Magento\View\Design\ThemeInterface $theme
     * @return $this
     */
    protected function _savePreviewImage(\Magento\View\Design\ThemeInterface $theme)
    {
        $themeDirectory = $theme->getCustomization()->getThemeFilesPath();
        if (!$theme->getPreviewImage() || !$themeDirectory) {
            return $this;
        }
        $imagePath = realpath($themeDirectory . DIRECTORY_SEPARATOR . $theme->getPreviewImage());
        if (0 === strpos($imagePath, $themeDirectory)) {
            $theme->getThemeImage()->createPreviewImage($imagePath);
        }
        return $this;
    }

    /**
     * Get theme from DB by full path
     *
     * @param string $fullPath
     * @return \Magento\Core\Model\Theme
     */
    public function getThemeFromDb($fullPath)
    {
        return $this->_collectionFactory->create()->getThemeByFullPath($fullPath);
    }

    /**
     * Checks all physical themes that they were not deleted
     *
     * @return \Magento\Core\Model\Theme\Registration
     */
    public function checkPhysicalThemes()
    {
        $themes = $this->_collectionFactory->create()->addTypeFilter(\Magento\Core\Model\Theme::TYPE_PHYSICAL);
        /** @var $theme \Magento\View\Design\ThemeInterface */
        foreach ($themes as $theme) {
            if (!$this->_themeCollection->hasTheme($theme)) {
                $theme->setType(\Magento\Core\Model\Theme::TYPE_VIRTUAL)->save();
            }
        }
        return $this;
    }

    /**
     * Check whether all themes have correct parent theme by type
     *
     * @return \Magento\Core\Model\Resource\Theme\Collection
     */
    public function checkAllowedThemeRelations()
    {
        foreach ($this->_forbiddenRelations as $typesSequence) {
            list($parentType, $childType) = $typesSequence;
            $collection = $this->_collectionFactory->create();
            $collection->addTypeRelationFilter($parentType, $childType);
            /** @var $theme \Magento\View\Design\ThemeInterface */
            foreach ($collection as $theme) {
                $parentId = $this->_getResetParentId($theme);
                if ($theme->getParentId() != $parentId) {
                    $theme->setParentId($parentId)->save();
                }
            }
        }
        return $this;
    }

    /**
     * Reset parent themes by type
     *
     * @param \Magento\View\Design\ThemeInterface $theme
     * @return int|null
     */
    protected function _getResetParentId(\Magento\View\Design\ThemeInterface $theme)
    {
        $parentTheme = $theme->getParentTheme();
        while ($parentTheme) {
            foreach ($this->_allowedRelations as $typesSequence) {
                list($parentType, $childType) = $typesSequence;
                if ($theme->getType() == $childType && $parentTheme->getType() == $parentType) {
                    return $parentTheme->getId();
                }
            }
            $parentTheme = $parentTheme->getParentTheme();
        }
        return null;
    }
}
