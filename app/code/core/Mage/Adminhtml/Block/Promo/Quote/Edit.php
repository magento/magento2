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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * description
 *
 * @category    Mage
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Promo_Quote_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'promo_quote';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('Mage_SalesRule_Helper_Data')->__('Save Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('Mage_SalesRule_Helper_Data')->__('Delete Rule'));

        $rule = Mage::registry('current_promo_quote_rule');

        if (!$rule || !$rule->isDeleteable()) {
            $this->_removeButton('delete');
        }

        if ($rule && $rule->isReadonly()) {
            $this->_removeButton('save');
            $this->_removeButton('reset');
        } else {
            $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('Mage_SalesRule_Helper_Data')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class' => 'save'
            ), 10);
            $this->_formScripts[] = " function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'back/edit/') } ";
        }

        #$this->setTemplate('promo/quote/edit.phtml');
    }

    public function getHeaderText()
    {
        $rule = Mage::registry('current_promo_quote_rule');
        if ($rule->getRuleId()) {
            return Mage::helper('Mage_SalesRule_Helper_Data')->__("Edit Rule '%s'", $this->escapeHtml($rule->getName()));
        }
        else {
            return Mage::helper('Mage_SalesRule_Helper_Data')->__('New Rule');
        }
    }

    public function getProductsJson()
    {
        return '{}';
    }
}
