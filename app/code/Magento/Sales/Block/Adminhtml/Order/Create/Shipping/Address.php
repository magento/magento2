<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Shipping;

/**
 * Adminhtml sales order create shipping address block
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
        return __('Shipping Address');
    }

    /**
     * Return Header CSS Class
     *
     * @return string
     * @since 2.0.0
     */
    public function getHeaderCssClass()
    {
        return 'head-shipping-address';
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getIsShipping()
    {
        return true;
    }

    /**
     * Same as billing address flag
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getIsAsBilling()
    {
        return $this->getCreateOrderModel()->getShippingAddress()->getSameAsBilling();
    }

    /**
     * Saving shipping address must be turned off, when it is the same as billing
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getDontSaveInAddressBook()
    {
        return $this->getIsAsBilling();
    }

    /**
     * Return Form Elements values
     *
     * @return array
     * @since 2.0.0
     */
    public function getFormValues()
    {
        return $this->getAddress()->getData();
    }

    /**
     * Return customer address id
     *
     * @return int|bool
     * @since 2.0.0
     */
    public function getAddressId()
    {
        return $this->getAddress()->getCustomerAddressId();
    }

    /**
     * Return address object
     *
     * @return \Magento\Quote\Model\Quote\Address
     * @since 2.0.0
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getIsDisabled()
    {
        return $this->getQuote()->isVirtual();
    }
}
