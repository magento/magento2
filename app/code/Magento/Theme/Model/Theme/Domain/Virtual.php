<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Virtual theme domain model
 */
namespace Magento\Theme\Model\Theme\Domain;

/**
 * Class \Magento\Theme\Model\Theme\Domain\Virtual
 *
 * @since 2.0.0
 */
class Virtual implements \Magento\Framework\View\Design\Theme\Domain\VirtualInterface
{
    /**
     * Virtual theme model instance
     *
     * @var \Magento\Framework\View\Design\ThemeInterface
     * @since 2.0.0
     */
    protected $_theme;

    /**
     * @var \Magento\Theme\Model\ThemeFactory $themeFactory
     * @since 2.0.0
     */
    protected $_themeFactory;

    /**
     * Staging theme model instance
     *
     * @var \Magento\Framework\View\Design\ThemeInterface
     * @since 2.0.0
     */
    protected $_stagingTheme;

    /**
     * @var \Magento\Theme\Model\CopyService
     * @since 2.0.0
     */
    protected $_themeCopyService;

    /**
     * Theme customization config
     *
     * @var \Magento\Theme\Model\Config\Customization
     * @since 2.0.0
     */
    protected $_customizationConfig;

    /**
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param \Magento\Theme\Model\ThemeFactory $themeFactory
     * @param \Magento\Theme\Model\CopyService $themeCopyService
     * @param \Magento\Theme\Model\Config\Customization $customizationConfig
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Design\ThemeInterface $theme,
        \Magento\Theme\Model\ThemeFactory $themeFactory,
        \Magento\Theme\Model\CopyService $themeCopyService,
        \Magento\Theme\Model\Config\Customization $customizationConfig
    ) {
        $this->_theme = $theme;
        $this->_themeFactory = $themeFactory;
        $this->_themeCopyService = $themeCopyService;
        $this->_customizationConfig = $customizationConfig;
    }

    /**
     * Get 'staging' theme
     *
     * @return \Magento\Framework\View\Design\ThemeInterface
     * @since 2.0.0
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
     * @return \Magento\Framework\View\Design\ThemeInterface
     * @since 2.0.0
     */
    public function getPhysicalTheme()
    {
        /** @var $parentTheme \Magento\Framework\View\Design\ThemeInterface */
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
     * @since 2.0.0
     */
    public function isAssigned()
    {
        return $this->_customizationConfig->isThemeAssignedToStore($this->_theme);
    }

    /**
     * Create 'staging' theme associated with current 'virtual' theme
     *
     * @return \Magento\Framework\View\Design\ThemeInterface
     * @since 2.0.0
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
                'type' => \Magento\Framework\View\Design\ThemeInterface::TYPE_STAGING,
            ]
        );
        $stagingTheme->save();
        return $stagingTheme;
    }
}
