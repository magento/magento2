<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Condition;

class Address extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $_directoryCountry;

    /**
     * @var \Magento\Directory\Model\Config\Source\Allregion
     */
    protected $_directoryAllregion;

    /**
     * @var \Magento\Shipping\Model\Config\Source\Allmethods
     */
    protected $_shippingAllmethods;

    /**
     * @var \Magento\Payment\Model\Config\Source\Allmethods
     */
    protected $_paymentAllmethods;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Directory\Model\Config\Source\Country $directoryCountry
     * @param \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion
     * @param \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods
     * @param \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Directory\Model\Config\Source\Country $directoryCountry,
        \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion,
        \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods,
        \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_directoryCountry = $directoryCountry;
        $this->_directoryAllregion = $directoryAllregion;
        $this->_shippingAllmethods = $shippingAllmethods;
        $this->_paymentAllmethods = $paymentAllmethods;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'base_subtotal' => __('Subtotal'),
            'total_qty' => __('Total Items Quantity'),
            'weight' => __('Total Weight'),
            'payment_method' => __('Payment Method'),
            'shipping_method' => __('Shipping Method'),
            'postcode' => __('Shipping Postcode'),
            'region' => __('Shipping Region'),
            'region_id' => __('Shipping State/Province'),
            'country_id' => __('Shipping Country'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Get attribute element
     *
     * @return $this
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'base_subtotal':
            case 'weight':
            case 'total_qty':
                return 'numeric';

            case 'shipping_method':
            case 'payment_method':
            case 'country_id':
            case 'region_id':
                return 'select';
        }
        return 'string';
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'shipping_method':
            case 'payment_method':
            case 'country_id':
            case 'region_id':
                return 'select';
        }
        return 'text';
    }

    /**
     * Get value select options
     *
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'country_id':
                    $options = $this->_directoryCountry->toOptionArray();
                    break;

                case 'region_id':
                    $options = $this->_directoryAllregion->toOptionArray();
                    break;

                case 'shipping_method':
                    $options = $this->_shippingAllmethods->toOptionArray();
                    break;

                case 'payment_method':
                    $options = $this->_paymentAllmethods->toOptionArray();
                    break;

                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $address = $model;
        if (!$address instanceof \Magento\Sales\Model\Quote\Address) {
            if ($model->getQuote()->isVirtual()) {
                $address = $model->getQuote()->getBillingAddress();
            } else {
                $address = $model->getQuote()->getShippingAddress();
            }
        }

        if ('payment_method' == $this->getAttribute() && !$address->hasPaymentMethod()) {
            $address->setPaymentMethod($model->getQuote()->getPayment()->getMethod());
        }

        return parent::validate($address);
    }
}
