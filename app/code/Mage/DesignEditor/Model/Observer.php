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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Observer for design editor module
 */
class Mage_DesignEditor_Model_Observer
{
    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_DesignEditor_Helper_Data
     */
    protected $_helper;

    /**
     * @param Magento_ObjectManager $objectManager
     * @param Mage_DesignEditor_Helper_Data $helper
     */
    public function __construct(
        Magento_ObjectManager $objectManager,
        Mage_DesignEditor_Helper_Data $helper
    ) {
        $this->_objectManager = $objectManager;
        $this->_helper        = $helper;
    }

    /**
     * Remove non-VDE JavaScript assets in design mode
     * Applicable in combination with enabled 'vde_design_mode' flag for 'head' block
     *
     * @param Varien_Event_Observer $event
     */
    public function clearJs(Varien_Event_Observer $event)
    {
        /** @var $layout Mage_Core_Model_Layout */
        $layout = $event->getEvent()->getLayout();
        $blockHead = $layout->getBlock('head');
        if (!$blockHead || !$blockHead->getData('vde_design_mode')) {
            return;
        }

        /** @var $page Mage_Core_Model_Page */
        $page = $this->_objectManager->get('Mage_Core_Model_Page');

        /** @var $pageAssets Mage_Page_Model_Asset_GroupedCollection */
        $pageAssets = $page->getAssets();

        $vdeAssets = array();
        foreach ($pageAssets->getGroups() as $group) {
            if ($group->getProperty('flag_name') == 'vde_design_mode') {
                $vdeAssets = array_merge($vdeAssets, $group->getAll());
            }
        }

        /** @var $nonVdeAssets Mage_Core_Model_Page_Asset_AssetInterface[] */
        $nonVdeAssets = array_diff_key($pageAssets->getAll(), $vdeAssets);

        foreach ($nonVdeAssets as $assetId => $asset) {
            if ($asset->getContentType() == Mage_Core_Model_View_Publisher::CONTENT_TYPE_JS) {
                $pageAssets->remove($assetId);
            }
        }
    }

    /**
     * Save quick styles
     *
     * @param Varien_Event_Observer $event
     */
    public function saveQuickStyles($event)
    {
        /** @var $configuration Mage_DesignEditor_Model_Editor_Tools_Controls_Configuration */
        $configuration = $event->getData('configuration');
        /** @var $theme Mage_Core_Model_Theme */
        $theme = $event->getData('theme');
        if ($configuration->getControlConfig() instanceof Mage_DesignEditor_Model_Config_Control_QuickStyles) {
            /** @var $renderer Mage_DesignEditor_Model_Editor_Tools_QuickStyles_Renderer */
            $renderer = $this->_objectManager->create('Mage_DesignEditor_Model_Editor_Tools_QuickStyles_Renderer');
            $content = $renderer->render($configuration->getAllControlsData());
            /** @var $cssService Mage_DesignEditor_Model_Theme_Customization_File_QuickStyleCss */
            $cssService = $this->_objectManager->create(
                'Mage_DesignEditor_Model_Theme_Customization_File_QuickStyleCss'
            );
            /** @var $singleFile Mage_Theme_Model_Theme_SingleFile */
            $singleFile = $this->_objectManager->create('Mage_Theme_Model_Theme_SingleFile',
                array('fileService' => $cssService));
            $singleFile->update($theme, $content);
        }
    }

    /**
     * Save time stamp of last change
     *
     * @param Varien_Event_Observer $event
     */
    public function saveChangeTime($event)
    {
        /** @var $theme Mage_Core_Model_Theme|null */
        $theme = $event->getTheme() ?: $event->getDataObject()->getTheme();
        /** @var $change Mage_DesignEditor_Model_Theme_Change */
        $change = $this->_objectManager->create('Mage_DesignEditor_Model_Theme_Change');
        if ($theme && $theme->getId()) {
            $change->loadByThemeId($theme->getId());
            $change->setThemeId($theme->getId())->setChangeTime(null);
            $change->save();
        }
    }

    /**
     * Copy additional information about theme change time
     *
     * @param Varien_Event_Observer $event
     */
    public function copyChangeTime($event)
    {
        /** @var $sourceTheme Mage_Core_Model_Theme|null */
        $sourceTheme = $event->getData('sourceTheme');
        /** @var $targetTheme Mage_Core_Model_Theme|null */
        $targetTheme = $event->getData('targetTheme');
        if ($sourceTheme && $targetTheme) {
            /** @var $sourceChange Mage_DesignEditor_Model_Theme_Change */
            $sourceChange = $this->_objectManager->create('Mage_DesignEditor_Model_Theme_Change');
            $sourceChange->loadByThemeId($sourceTheme->getId());
            /** @var $targetChange Mage_DesignEditor_Model_Theme_Change */
            $targetChange = $this->_objectManager->create('Mage_DesignEditor_Model_Theme_Change');
            $targetChange->loadByThemeId($targetTheme->getId());

            if ($sourceChange->getId()) {
                $targetChange->setThemeId($targetTheme->getId());
                $targetChange->setChangeTime($sourceChange->getChangeTime());
                $targetChange->save();
            } elseif ($targetChange->getId()) {
                $targetChange->delete();
            }
        }
    }

    /**
     * Determine if the vde specific translation class should be used.
     *
     * @param  Varien_Event_Observer $observer
     * @return Mage_DesignEditor_Model_Observer
     */
    public function initializeTranslation(Varien_Event_Observer $observer)
    {
        if ($this->_helper->isVdeRequest()) {
            // Request is for vde.  Override the translation class.
            $observer->getResult()->setInlineType('Mage_DesignEditor_Model_Translate_InlineVde');
        }
        return $this;
    }
}
