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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Invoice view  comments form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Comments_View extends Mage_Adminhtml_Block_Template
{
    /**
     * Retrieve required options from parent
     */
    protected function _beforeToHtml()
    {
        if (!$this->getParentBlock()) {
            Mage::throwException(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Invalid parent block for this block.'));
        }
        $this->setEntity($this->getParentBlock()->getSource());
        parent::_beforeToHtml();
    }

    /**
     * Prepare child blocks
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Invoice_Create_Items
     */
    protected function _prepareLayout()
    {
        $this->addChild('submit_button', 'Mage_Adminhtml_Block_Widget_Button', array(
            'id'      => 'submit_comment_button',
            'label'   => Mage::helper('Mage_Sales_Helper_Data')->__('Submit Comment'),
            'class'   => 'save'
        ));


        return parent::_prepareLayout();
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/addComment',array('id'=>$this->getEntity()->getId()));
    }

    public function canSendCommentEmail()
    {
        $helper = Mage::helper('Mage_Sales_Helper_Data');
        switch ($this->getParentType()) {
            case 'invoice':
                return $helper->canSendInvoiceCommentEmail($this->getEntity()->getOrder()->getStore()->getId());
            case 'shipment':
                return $helper->canSendShipmentCommentEmail($this->getEntity()->getOrder()->getStore()->getId());
            case 'creditmemo':
                return $helper->canSendCreditmemoCommentEmail($this->getEntity()->getOrder()->getStore()->getId());
        }

        return true;
    }
}
