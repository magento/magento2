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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Block_System_Design_Edit extends Mage_Adminhtml_Block_Widget
{

    protected $_template = 'system/design/edit.phtml';

    protected function _construct()
    {
        parent::_construct();

        $this->setId('design_edit');
    }

    protected function _prepareLayout()
    {
        $this->addChild('back_button', 'Mage_Adminhtml_Block_Widget_Button', array(
            'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Back'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/*/').'\')',
            'class' => 'back'
        ));

        $this->addChild('save_button', 'Mage_Adminhtml_Block_Widget_Button', array(
            'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Save'),
            'onclick'   => 'designForm.submit()',
            'class' => 'save'
        ));

        $this->addChild('delete_button', 'Mage_Adminhtml_Block_Widget_Button', array(
            'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Delete'),
            'onclick'   => 'confirmSetLocation(\''.Mage::helper('Mage_Core_Helper_Data')->__('Are you sure?').'\', \''.$this->getDeleteUrl().'\')',
            'class'  => 'delete'
        ));
        return parent::_prepareLayout();
    }

    public function getDesignChangeId()
    {
        return Mage::registry('design')->getId();
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array('_current'=>true));
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true));
    }

    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', array('_current'=>true));
    }

    public function getHeader()
    {
        $header = '';
        if (Mage::registry('design')->getId()) {
            $header = Mage::helper('Mage_Core_Helper_Data')->__('Edit Design Change');
        } else {
            $header = Mage::helper('Mage_Core_Helper_Data')->__('New Design Change');
        }
        return $header;
    }
}
