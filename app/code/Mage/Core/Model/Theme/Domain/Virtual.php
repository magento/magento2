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
 * Virtual theme domain model
 */
class Mage_Core_Model_Theme_Domain_Virtual
{
    /**
     * Virtual theme model instance
     *
     * @var Mage_Core_Model_Theme
     */
    protected $_theme;

    /**
     * @var Mage_Core_Model_Theme_Factory $themeFactory
     */
    protected $_themeFactory;

    /**
     * Staging theme model instance
     *
     * @var Mage_Core_Model_Theme
     */
    protected $_stagingTheme;

    /**
     * @var Mage_Core_Model_Theme_CopyService
     */
    protected $_themeCopyService;

    /**
     * Theme service model
     *
     * @var Mage_Core_Model_Theme_Service
     */
    protected $_service;

    /**
     * @param Mage_Core_Model_Theme $theme
     * @param Mage_Core_Model_Theme_Factory $themeFactory
     * @param Mage_Core_Model_Theme_CopyService $themeCopyService
     * @param Mage_Core_Model_Theme_Service $service
     */
    public function __construct(
        Mage_Core_Model_Theme $theme,
        Mage_Core_Model_Theme_Factory $themeFactory,
        Mage_Core_Model_Theme_CopyService $themeCopyService,
        Mage_Core_Model_Theme_Service $service
    ) {
        $this->_theme = $theme;
        $this->_themeFactory = $themeFactory;
        $this->_themeCopyService = $themeCopyService;
        $this->_service = $service;
    }

    /**
     * Get 'staging' theme
     *
     * @return Mage_Core_Model_Theme
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
     * Check if theme is assigned to ANY store
     *
     * @return bool
     */
    public function isAssigned()
    {
        return $this->_service->isThemeAssignedToStore($this->_theme);
    }

    /**
     * Create 'staging' theme associated with current 'virtual' theme
     *
     * @return Mage_Core_Model_Theme
     */
    protected function _createStagingTheme()
    {
        $stagingTheme = $this->_themeFactory->create();
        $stagingTheme->setData(array(
            'parent_id'            => $this->_theme->getId(),
            'theme_path'           => null,
            'theme_version'        => $this->_theme->getThemeVersion(),
            'theme_title'          => sprintf('%s - Staging', $this->_theme->getThemeTitle()),
            'preview_image'        => $this->_theme->getPreviewImage(),
            'magento_version_from' => $this->_theme->getMagentoVersionFrom(),
            'magento_version_to'   => $this->_theme->getMagentoVersionTo(),
            'is_featured'          => $this->_theme->getIsFeatured(),
            'area'                 => $this->_theme->getArea(),
            'type'                 => Mage_Core_Model_Theme::TYPE_STAGING
        ));
        $stagingTheme->save();
        return $stagingTheme;
    }
}
