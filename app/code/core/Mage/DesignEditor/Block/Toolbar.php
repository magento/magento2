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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Frontend toolbar panel for the design editor controls
 */
class Mage_DesignEditor_Block_Toolbar extends Mage_Core_Block_Template
{
    /**
     * Prevent rendering if the design editor is inactive
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var $session Mage_DesignEditor_Model_Session */
        $session = Mage::getSingleton('Mage_DesignEditor_Model_Session');
        if (!$session->isDesignEditorActive()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Returns messages for Visual Design Editor, clears list of session messages
     *
     * @return array
     */
    public function getMessages()
    {
        return Mage::getSingleton('Mage_DesignEditor_Model_Session')
            ->getMessages(true)
            ->getItems();
    }

    /**
     * Get configuration options for Visual Design Editor as JSON
     *
     * @return string
     */
    public function getOptionsJson()
    {
        $options = array(
            'cookieHighlightingName' => Mage_DesignEditor_Model_Session::COOKIE_HIGHLIGHTING,
        );
        /** @var $toolbarRowBlock Mage_DesignEditor_Block_Template */
        $toolbarRowBlock = $this->getChildBlock('design_editor_toolbar_row');

        if ($toolbarRowBlock) {
            /** @var $buttonsBlock Mage_DesignEditor_Block_Toolbar_Buttons */
            $buttonsBlock = $toolbarRowBlock->getChildBlock('design_editor_toolbar_buttons');
            if ($buttonsBlock) {
                $options['compactLogUrl'] = $buttonsBlock->getCompactLogUrl();
                $options['viewLayoutUrl'] = $buttonsBlock->getViewLayoutUrl();
                $options['baseUrl'] = Mage::getBaseUrl();
            }
        }

        return Mage::helper('Mage_Core_Helper_Data')->jsonEncode($options);
    }
}
