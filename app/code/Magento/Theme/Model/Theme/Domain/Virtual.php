<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Virtual theme domain model
 */
namespace Magento\Theme\Model\Theme\Domain;

use Magento\Framework\View\Design\Theme\Domain\VirtualInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Config\Customization;
use Magento\Theme\Model\CopyService;
use Magento\Theme\Model\ThemeFactory;

class Virtual implements VirtualInterface
{
    /**
     * Virtual theme model instance
     *
     * @var ThemeInterface
     */
    protected $_theme;

    /**
     * @var ThemeFactory $themeFactory
     */
    protected $_themeFactory;

    /**
     * Staging theme model instance
     *
     * @var ThemeInterface
     */
    protected $_stagingTheme;

    /**
     * @var CopyService
     */
    protected $_themeCopyService;

    /**
     * Theme customization config
     *
     * @var Customization
     */
    protected $_customizationConfig;

    /**
     * @param ThemeInterface $theme
     * @param ThemeFactory $themeFactory
     * @param CopyService $themeCopyService
     * @param Customization $customizationConfig
     */
    public function __construct(
        ThemeInterface $theme,
        ThemeFactory $themeFactory,
        CopyService $themeCopyService,
        Customization $customizationConfig
    ) {
        $this->_theme = $theme;
        $this->_themeFactory = $themeFactory;
        $this->_themeCopyService = $themeCopyService;
        $this->_customizationConfig = $customizationConfig;
    }

    /**
     * Get 'staging' theme
     *
     * @return ThemeInterface
     */
    public function getStagingTheme()
    {
        if (!$this->_stagingTheme) {
            $this->_stagingTheme = $this->_theme->getStagingVersion();
            if (!$this->_stagingTheme) {
                $this->_stagingTheme = $this->_createStagingTheme();
                $this->_themeCopyService->copy($this->_theme, $this->_stagingTheme);
            }
        }
        return $this->_stagingTheme;
    }

    /**
     * Get 'physical' theme
     *
     * @return ThemeInterface
     */
    public function getPhysicalTheme()
    {
        /** @var ThemeInterface $parentTheme */
        $parentTheme = $this->_theme->getParentTheme();
        while ($parentTheme && !$parentTheme->isPhysical()) {
            $parentTheme = $parentTheme->getParentTheme();
        }

        if (!$parentTheme || !$parentTheme->getId()) {
            return null;
        }

        return $parentTheme;
    }

    /**
     * Check if theme is assigned to ANY store
     *
     * @return bool
     */
    public function isAssigned()
    {
        return $this->_customizationConfig->isThemeAssignedToStore($this->_theme);
    }

    /**
     * Create 'staging' theme associated with current 'virtual' theme
     *
     * @return ThemeInterface
     */
    protected function _createStagingTheme()
    {
        $stagingTheme = $this->_themeFactory->create();
        $stagingTheme->setData(
            [
                'parent_id' => $this->_theme->getId(),
                'theme_path' => null,
                'theme_title' => sprintf('%s - Staging', $this->_theme->getThemeTitle()),
                'preview_image' => $this->_theme->getPreviewImage(),
                'is_featured' => $this->_theme->getIsFeatured(),
                'type' => ThemeInterface::TYPE_STAGING,
            ]
        );
        $stagingTheme->save();
        return $stagingTheme;
    }
}
