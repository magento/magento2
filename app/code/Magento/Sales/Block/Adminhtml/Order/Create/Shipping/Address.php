<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Shipping;

/**
 * Adminhtml sales order create shipping address block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Address extends \Magento\Sales\Block\Adminhtml\Order\Create\Form\Address
{
    /**
     * Return header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return __('Shipping Address');
    }

    /**
     * Return Header CSS Class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-shipping-address';
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $this->setJsVariablePrefix('shippingAddress');
        parent::_prepareForm();

        $this->_form->addFieldNameSuffix('order[shipping_address]');
        $this->_form->setHtmlNamePrefix('order[shipping_address]');
        $this->_form->setHtmlIdPrefix('order-shipping_address_');

        return $this;
    }

    /**
     * Return is shipping address flag
     *
     * @return true
     */
    public function getIsShipping()
    {
        return true;
    }

    /**
     * Same as billing address flag
     *
     * @return bool
     */
    public function getIsAsBilling()
    {
        return $this->getCreateOrderModel()->getShippingAddress()->getSameAsBilling();
    }

    /**
     * Saving shipping address must be turned off, when it is the same as billing
     *
     * @return bool
     */
    public function getDontSaveInAddressBook()
    {
        return $this->getIsAsBilling();
    }

    /**
     * Return Form Elements values
     *
     * @return array
     */
    public function getFormValues()
    {
        return $this->getAddress()->getData();
    }

    /**
     * Return customer address id
     *
     * @return int|bool
     */
    public function getAddressId()
    {
        return $this->getAddress()->getCustomerAddressId();
    }

    /**
     * Return address object
     *
     * @return \Magento\Sales\Model\Quote\Address
     */
    public function getAddress()
    {
        if ($this->getIsAsBilling()) {
            $address = $this->getCreateOrderModel()->getBillingAddress();
        } else {
            $address = $this->getCreateOrderModel()->getShippingAddress();
        }
        return $address;
    }

    /**
     * Return is address disabled flag
     * Return true is the quote is virtual
     *
     * @return bool
     */
    public function getIsDisabled()
    {
        return $this->getQuote()->isVirtual();
    }
}
