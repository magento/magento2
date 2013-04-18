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
 * @package     Mage_User
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_User_Block_Buttons extends Mage_Backend_Block_Template
{

    protected function _prepareLayout()
    {
        $this->addChild('backButton', 'Mage_Backend_Block_Widget_Button', array(
            'label'     => Mage::helper('Mage_User_Helper_Data')->__('Back'),
            'onclick'   => 'window.location.href=\''.$this->getUrl('*/*/').'\'',
            'class' => 'back'
        ));

        $this->addChild('resetButton', 'Mage_Backend_Block_Widget_Button', array(
            'label'     => Mage::helper('Mage_User_Helper_Data')->__('Reset'),
            'onclick'   => 'window.location.reload()'
        ));

        $this->addChild('saveButton', 'Mage_Backend_Block_Widget_Button', array(
            'label'     => Mage::helper('Mage_User_Helper_Data')->__('Save Role'),
            'class' => 'save',
            'data_attribute'  => array(
                'mage-init' => array(
                    'button' => array('event' => 'save', 'target' => '#role-edit-form'),
                ),
            ),
        ));

        $this->addChild('deleteButton', 'Mage_Backend_Block_Widget_Button', array(
            'label'     => Mage::helper('Mage_User_Helper_Data')->__('Delete Role'),
            'onclick'   => 'deleteConfirm(\''
                . Mage::helper('Mage_User_Helper_Data')->__('Are you sure you want to do this?')
                . '\', \''
                . $this->getUrl('*/*/delete', array('rid' => $this->getRequest()->getParam('rid')))
                . '\')',
            'class' => 'delete'
        ));
        return parent::_prepareLayout();
    }

    public function getBackButtonHtml()
    {
        return $this->getChildHtml('backButton');
    }

    public function getResetButtonHtml()
    {
        return $this->getChildHtml('resetButton');
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('saveButton');
    }

    public function getDeleteButtonHtml()
    {
        if (intval($this->getRequest()->getParam('rid')) == 0 ) {
            return;
        }
        return $this->getChildHtml('deleteButton');
    }

    public function getUser()
    {
        return Mage::registry('user_data');
    }
}
