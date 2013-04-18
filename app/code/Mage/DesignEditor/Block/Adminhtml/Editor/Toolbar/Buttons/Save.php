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
 * Save button block
 */
class Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_Buttons_Save
    extends Mage_Backend_Block_Widget_Button_Split
{
    /**
     * Current theme used for preview
     *
     * @var Mage_Core_Model_Theme
     */
    protected $_theme;

    /**
     * Init save button
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function init()
    {
        $theme = $this->getTheme();
        $themeType = $theme->getType();
        if ($themeType == Mage_Core_Model_Theme::TYPE_PHYSICAL) {
            $this->_initPhysical();
        } else if ($themeType == Mage_Core_Model_Theme::TYPE_VIRTUAL) {
            if ($theme->getDomainModel(Mage_Core_Model_Theme::TYPE_VIRTUAL)->isAssigned()) {
                $this->_initAssigned();
            } else {
                $this->_initUnAssigned();
            }
        } else {
            throw new InvalidArgumentException(
                sprintf('Invalid theme of a "%s" type passed to save button block', $themeType)
            );
        }

        return $this;
    }

    /**
     * Get current theme
     *
     * @return Mage_Core_Model_Theme
     * @throws InvalidArgumentException
     */
    public function getTheme()
    {
        if (null === $this->_theme) {
            throw new InvalidArgumentException('Current theme was not passed to save button');
        }
        return $this->_theme;
    }

    /**
     * Set current theme
     *
     * @param Mage_Core_Model_Theme $theme
     * @return Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_Buttons
     */
    public function setTheme($theme)
    {
        $this->_theme = $theme;

        return $this;
    }

    /**
     * Whether button is disabled
     *
     * @return mixed
     */
    public function getDisabled()
    {
        return false;
    }

    /**
     * Disable actions-split functionality if no options provided
     *
     *
     * @return bool
     */
    public function hasSplit()
    {
        $options = $this->getOptions();
        return is_array($options) && count($options) > 0;
    }

    /**
     * Get URL to apply changes from 'staging' theme to 'virtual' theme
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/system_design_editor/save', array('theme_id' => $this->getTheme()->getId()));
    }

    /**
     * Init 'Save' button for 'physical' theme
     *
     * @return $this
     */
    protected function _initPhysical()
    {
        $this->setData(array(
            'label'          => $this->__('Assign'),
            'data_attribute' => array('mage-init' => $this->_getAssignInitData()),
            'options'        => array()
        ));

        return $this;
    }

    /**
     * Init 'Save' button for 'virtual' theme assigned to a store
     *
     * @return $this
     */
    protected function _initAssigned()
    {
        $this->setData(array(
            'label'          => $this->__('Save'),
            'data_attribute' => array('mage-init' => $this->_getSaveAndAssignInitData()),
            'options'        => array()
        ));

        return $this;
    }

    /**
     * Init 'Save' button for 'virtual' theme assigned to a store
     *
     * @return $this
     */
    protected function _initUnAssigned()
    {
        $this->setData(array(
            'label'          => $this->__('Save'),
            'data_attribute' => array('mage-init' => $this->_getSaveInitData()),
            'options'        => array(
                array(
                    'label'          => $this->__('Save'),
                    'data_attribute' => array('mage-init' => $this->_getSaveInitData()),
                    'disabled'       => true
                ),
                array(
                    'label'          => $this->__('Save and Assign'),
                    'data_attribute' => array('mage-init' => $this->_getSaveAndAssignInitData())
                ),
            )
        ));

        return $this;
    }

    /**
     * Get 'data-mage-init' attribute value for 'Save' button
     *
     * @return string
     */
    protected function _getSaveInitData()
    {
        $message = "You are about to apply current changes for your live store, are you sure want to do this?";
        $data = array(
            'button' => array(
                'event'     => 'save',
                'target'    => 'body',
                'eventData' => array(
                    'theme_id' => $this->getTheme()->getId(),
                    'save_url' => $this->getSaveUrl(),
                    'confirm_message' => $this->__($message)
                )
            ),
        );

        return $this->_encode($data);
    }

    /**
     * Get 'data-mage-init' attribute value for 'Save' button
     *
     * @return string
     */
    protected function _getAssignInitData()
    {
        $message = "You are about to apply this theme for your live store, are you sure want to do this?\n\n" .
            'Note: copy of the current theme will be created automatically and assigned to your store, ' .
            'so you can change your copy as you wish';
        $data = array(
            'button' => array(
                'event'     => 'assign',
                'target'    => 'body',
                'eventData' => array(
                    'theme_id'        => $this->getTheme()->getId(),
                    'confirm_message' => $this->__($message)
                )
            ),
        );

        return $this->_encode($data);
    }

    /**
     * Get 'data-mage-init' attribute value for 'Save and Assign' button
     *
     * @return string
     */
    protected function _getSaveAndAssignInitData()
    {
        $message = "You are about to apply current changes for your live store, are you sure want to do this?";

        $data = array(
            'button' => array(
                'event'     => 'save-and-assign',
                'target'    => 'body',
                'eventData' => array(
                    'theme_id' => $this->getTheme()->getId(),
                    'save_url' => $this->getSaveUrl(),
                    'confirm_message' => $this->__($message)
                )
            ),
        );

        return $this->_encode($data);
    }

    /**
     * Get encoded data string
     *
     * @param array $data
     * @return string
     */
    protected function _encode($data)
    {
        return $this->helper('Mage_Backend_Helper_Data')->escapeHtml(json_encode($data));
    }
}
