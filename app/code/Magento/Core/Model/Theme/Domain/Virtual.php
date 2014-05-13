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

/**
 * Virtual theme domain model
 */
namespace Magento\Core\Model\Theme\Domain;

class Virtual implements \Magento\Framework\View\Design\Theme\Domain\VirtualInterface
{
    /**
     * Virtual theme model instance
     *
     * @var \Magento\Framework\View\Design\ThemeInterface
     */
    protected $_theme;

    /**
     * @var \Magento\Core\Model\ThemeFactory $themeFactory
     */
    protected $_themeFactory;

    /**
     * Staging theme model instance
     *
     * @var \Magento\Framework\View\Design\ThemeInterface
     */
    protected $_stagingTheme;

    /**
     * @var \Magento\Theme\Model\CopyService
     */
    protected $_themeCopyService;

    /**
     * Theme customization config
     *
     * @var \Magento\Theme\Model\Config\Customization
     */
    protected $_customizationConfig;

    /**
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param \Magento\Core\Model\ThemeFactory $themeFactory
     * @param \Magento\Theme\Model\CopyService $themeCopyService
     * @param \Magento\Theme\Model\Config\Customization $customizationConfig
     */
    public function __construct(
        \Magento\Framework\View\Design\ThemeInterface $theme,
        \Magento\Core\Model\ThemeFactory $themeFactory,
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
     */
    public function isAssigned()
    {
        return $this->_customizationConfig->isThemeAssignedToStore($this->_theme);
    }

    /**
     * Create 'staging' theme associated with current 'virtual' theme
     *
     * @return \Magento\Framework\View\Design\ThemeInterface
     */
    protected function _createStagingTheme()
    {
        $stagingTheme = $this->_themeFactory->create();
        $stagingTheme->setData(
            array(
                'parent_id' => $this->_theme->getId(),
                'theme_path' => null,
                'theme_version' => $this->_theme->getThemeVersion(),
                'theme_title' => sprintf('%s - Staging', $this->_theme->getThemeTitle()),
                'preview_image' => $this->_theme->getPreviewImage(),
                'is_featured' => $this->_theme->getIsFeatured(),
                'type' => \Magento\Framework\View\Design\ThemeInterface::TYPE_STAGING
            )
        );
        $stagingTheme->save();
        return $stagingTheme;
    }
}
