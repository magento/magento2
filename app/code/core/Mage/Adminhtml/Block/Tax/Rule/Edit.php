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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml tax rule Edit Container
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Tax_Rule_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Init class
     *
     */
    protected function _construct()
    {
        $this->_objectId = 'rule';
        $this->_controller = 'tax_rule';

        parent::_construct();

        $this->_updateButton('save', 'label', Mage::helper('Mage_Tax_Helper_Data')->__('Save Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('Mage_Tax_Helper_Data')->__('Delete Rule'));

        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('Mage_Tax_Helper_Data')->__('Save and Continue Edit'),
            'class' => 'save',
            'data_attr'  => array(
                'widget-button' => array('event' => 'saveAndContinueEdit', 'related' => '#edit_form'),
            ),
        ), 10);
    }

    /**
     * Get Header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('tax_rule')->getId()) {
            return Mage::helper('Mage_Tax_Helper_Data')->__("Edit Rule");
        }
        else {
            return Mage::helper('Mage_Tax_Helper_Data')->__('New Rule');
        }
    }
}
