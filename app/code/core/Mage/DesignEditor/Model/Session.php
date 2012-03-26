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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Design editor session model
 */
class Mage_DesignEditor_Model_Session extends Mage_Admin_Model_Session
{
    /**
     * Session key that indicates whether the design editor is active
     */
    const SESSION_DESIGN_EDITOR_ACTIVE = 'DESIGN_EDITOR_ACTIVE';

    /**
     * Check whether the design editor is active for the current session or not
     *
     * @return bool
     */
    public function isDesignEditorActive()
    {
        return $this->getData(self::SESSION_DESIGN_EDITOR_ACTIVE) && $this->isLoggedIn();
    }

    /**
     * Activate the design editor for the current session
     */
    public function activateDesignEditor()
    {
        if (!$this->getData(self::SESSION_DESIGN_EDITOR_ACTIVE) && $this->isLoggedIn()) {
            $this->setData(self::SESSION_DESIGN_EDITOR_ACTIVE, 1);
            Mage::dispatchEvent('design_editor_session_activate');
        }
    }

    /**
     * Deactivate the design editor for the current session
     */
    public function deactivateDesignEditor()
    {
        /*
         * isLoggedIn() is intentionally not taken into account to be able to trigger event when admin session expires
         */
        if ($this->getData(self::SESSION_DESIGN_EDITOR_ACTIVE)) {
            $this->unsetData(self::SESSION_DESIGN_EDITOR_ACTIVE);
            Mage::dispatchEvent('design_editor_session_deactivate');
        }
    }

    /**
     * Sets skin to user session, so that next time everything will be rendered with this skin
     *
     * @param string $skin
     * @return Mage_DesignEditor_Model_Session
     */
    public function setSkin($skin)
    {
        if ($skin && !$this->_isSkinApplicable($skin)) {
            Mage::throwException(Mage::helper('Mage_DesignEditor_Helper_Data')->__("Skin doesn't exist"));
        }
        $this->setData('skin', $skin);
        return $this;
    }

    /**
     * Returns whether a skin is a valid one to set into user session
     *
     * @param string $skin
     * @return bool
     */
    protected function _isSkinApplicable($skin)
    {
        if (!$skin) {
            return false;
        }
        $options = Mage::getModel('Mage_Core_Model_Design_Source_Design')->getOptions();
        foreach ($options as $optGroup) {
            foreach ($optGroup['value'] as $option) {
                if ($option['value'] == $skin) {
                    return true;
                }
            }
        }
        return false;
    }
}
