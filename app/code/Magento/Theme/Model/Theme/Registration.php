<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

use Magento\Framework\Filesystem;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Design\ThemeInterface;

/**
 * Theme registration model class
 * @since 2.0.0
 */
class Registration
{
    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\Data\CollectionFactory
     * @since 2.0.0
     */
    protected $_collectionFactory;

    /**
     * Collection of themes in file-system
     *
     * @var Collection
     * @since 2.0.0
     */
    protected $_themeCollection;

    /**
     * Allowed sequence relation by type, array(parent theme, child theme)
     *
     * @var array
     * @since 2.0.0
     */
    protected $_allowedRelations = [
        [ThemeInterface::TYPE_PHYSICAL, ThemeInterface::TYPE_VIRTUAL],
        [ThemeInterface::TYPE_VIRTUAL, ThemeInterface::TYPE_STAGING],
    ];

    /**
     * Forbidden sequence relation by type
     *
     * @var array
     * @since 2.0.0
     */
    protected $_forbiddenRelations = [
        [ThemeInterface::TYPE_VIRTUAL, ThemeInterface::TYPE_VIRTUAL],
        [ThemeInterface::TYPE_PHYSICAL, ThemeInterface::TYPE_STAGING],
    ];

    /**
     * Initialize dependencies
     *
     * @param \Magento\Theme\Model\ResourceModel\Theme\Data\CollectionFactory $collectionFactory
     * @param \Magento\Theme\Model\Theme\Data\Collection $filesystemCollection
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Theme\Model\ResourceModel\Theme\Data\CollectionFactory $collectionFactory,
        \Magento\Theme\Model\Theme\Data\Collection $filesystemCollection
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_themeCollection = $filesystemCollection;
    }

    /**
     * Theme registration
     *
     * @return $this
     * @since 2.0.0
     */
    public function register()
    {
        $this->_themeCollection->clear();

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
     * @param ThemeInterface &$theme
     * @param array $inheritanceChain
     * @return $this
     * @throws LocalizedException
     * @since 2.0.0
     */
    protected function _registerThemeRecursively(&$theme, $inheritanceChain = [])
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
            throw new LocalizedException(__('Circular-reference in theme inheritance detected for "%1"', $tempId));
        }
        $inheritanceChain[] = $tempId;
        $parentTheme = $theme->getParentTheme();
        if ($parentTheme) {
            $this->_registerThemeRecursively($parentTheme, $inheritanceChain);
            $theme->setParentId($parentTheme->getId());
        }

        $this->_savePreviewImage($theme);
        $theme->setType(ThemeInterface::TYPE_PHYSICAL);
        $theme->save();

        return $this;
    }

    /**
     * Save preview image for theme
     *
     * @param ThemeInterface $theme
     * @return $this
     * @since 2.0.0
     */
    protected function _savePreviewImage(ThemeInterface $theme)
    {
        $themeDirectory = $theme->getCustomization()->getThemeFilesPath();
        if (!$theme->getPreviewImage() || !$themeDirectory) {
            return $this;
        }
        $imagePath = $themeDirectory . '/' . $theme->getPreviewImage();
        if (0 === strpos($imagePath, $themeDirectory)) {
            $theme->getThemeImage()->createPreviewImage($imagePath);
        }
        return $this;
    }

    /**
     * Get theme from DB by full path
     *
     * @param string $fullPath
     * @return ThemeInterface
     * @since 2.0.0
     */
    public function getThemeFromDb($fullPath)
    {
        return $this->_collectionFactory->create()->getThemeByFullPath($fullPath);
    }

    /**
     * Checks all physical themes that they were not deleted
     *
     * @return $this
     * @since 2.0.0
     */
    public function checkPhysicalThemes()
    {
        $themes = $this->_collectionFactory->create()->addTypeFilter(ThemeInterface::TYPE_PHYSICAL);
        /** @var $theme ThemeInterface */
        foreach ($themes as $theme) {
            if (!$this->_themeCollection->hasTheme($theme)) {
                $theme->setType(ThemeInterface::TYPE_VIRTUAL)->save();
            }
        }
        return $this;
    }

    /**
     * Check whether all themes have correct parent theme by type
     *
     * @return $this
     * @since 2.0.0
     */
    public function checkAllowedThemeRelations()
    {
        foreach ($this->_forbiddenRelations as $typesSequence) {
            list($parentType, $childType) = $typesSequence;
            $collection = $this->_collectionFactory->create();
            $collection->addTypeRelationFilter($parentType, $childType);
            /** @var $theme ThemeInterface */
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
     * @param ThemeInterface $theme
     * @return int|null
     * @since 2.0.0
     */
    protected function _getResetParentId(ThemeInterface $theme)
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
