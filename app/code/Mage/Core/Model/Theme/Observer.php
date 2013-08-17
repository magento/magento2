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
 * Theme Observer model
 */
class Mage_Core_Model_Theme_Observer
{
    /**
     * @var Mage_Core_Model_Theme_ImageFactory
     */
    protected $_themeImageFactory;

    /**
     * @var Mage_Core_Model_Resource_Layout_Update_Collection
     */
    protected $_updateCollection;

    /**
     * @var Mage_Theme_Model_Config_Customization
     */
    protected $_themeConfig;

    /**
     * @var Mage_Core_Model_Event_Manager
     */
    protected $_eventDispatcher;

    /**
     * @var
     */
    protected $_helper;

    /**
     * @param Mage_Core_Model_Theme_ImageFactory $themeImageFactory
     * @param Mage_Core_Model_Resource_Layout_Update_Collection $updateCollection
     * @param Mage_Theme_Model_Config_Customization $themeConfig
     * @param Mage_Core_Model_Event_Manager $eventDispatcher
     * @param Mage_Core_Helper_Data $helper
     */
    public function __construct(
        Mage_Core_Model_Theme_ImageFactory $themeImageFactory,
        Mage_Core_Model_Resource_Layout_Update_Collection $updateCollection,
        Mage_Theme_Model_Config_Customization $themeConfig,
        Mage_Core_Model_Event_Manager $eventDispatcher,
        Mage_Core_Helper_Data $helper
    ) {
        $this->_themeImageFactory = $themeImageFactory;
        $this->_updateCollection = $updateCollection;
        $this->_themeConfig = $themeConfig;
        $this->_eventDispatcher = $eventDispatcher;
        $this->_helper = $helper;
    }

    /**
     * Clean related contents to a theme (before save)
     *
     * @param Varien_Event_Observer $observer
     * @throws Mage_Core_Exception
     */
    public function cleanThemeRelatedContent(Varien_Event_Observer $observer)
    {
        $theme = $observer->getEvent()->getData('theme');
        if ($theme instanceof Mage_Core_Model_Theme) {
            return;
        }
        /** @var $theme Mage_Core_Model_Theme */
        if ($this->_themeConfig->isThemeAssignedToStore($theme)) {
            throw new Mage_Core_Exception($this->_helper->__('Theme isn\'t deletable.'));
        }
        $this->_themeImageFactory->create(array('theme' => $theme))->removePreviewImage();
        $this->_updateCollection->addThemeFilter($theme->getId())->delete();
    }

    /**
     * Check a theme, it's assigned to any of store
     *
     * @param Varien_Event_Observer $observer
     */
    public function checkThemeIsAssigned(Varien_Event_Observer $observer)
    {
        $theme = $observer->getEvent()->getData('theme');
        if ($theme instanceof Mage_Core_Model_Theme) {
            /** @var $theme Mage_Core_Model_Theme */
            if ($this->_themeConfig->isThemeAssignedToStore($theme)) {
                $this->_eventDispatcher->dispatch('assigned_theme_changed', array('theme' => $this));
            }
        }
    }
}
