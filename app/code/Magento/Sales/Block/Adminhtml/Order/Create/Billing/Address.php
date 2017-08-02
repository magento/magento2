<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Billing;

/**
 * Adminhtml sales order create billing address block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 2.0.0
 */
class Address extends \Magento\Sales\Block\Adminhtml\Order\Create\Form\Address
{
    /**
     * Return header text
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        return __('Billing Address');
    }

    /**
     * Return Header CSS Class
     *
     * @return string
     * @since 2.0.0
     */
    public function getHeaderCssClass()
    {
        return 'head-billing-address';
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareForm()
    {
        $this->setJsVariablePrefix('billingAddress');
        parent::_prepareForm();

        $this->_form->addFieldNameSuffix('order[billing_address]');
        $this->_form->setHtmlNamePrefix('order[billing_address]');
        $this->_form->setHtmlIdPrefix('order-billing_address_');

        return $this;
    }

    /**
     * Return Form Elements values
     *
     * @return array
     * @since 2.0.0
     */
    public function getFormValues()
    {
        return $this->getCreateOrderModel()->getBillingAddress()->getData();
    }

    /**
     * Return customer address id
     *
     * @return int|bool
     * @since 2.0.0
     */
    public function getAddressId()
    {
        return $this->getCreateOrderModel()->getBillingAddress()->getCustomerAddressId();
    }

    /**
     * Return billing address object
     *
     * @return \Magento\Quote\Model\Quote\Address
     * @since 2.0.0
     */
    public function getAddress()
    {
        return $this->getCreateOrderModel()->getBillingAddress();
    }
}
