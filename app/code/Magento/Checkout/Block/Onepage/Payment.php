<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Onepage;

/**
 * One page checkout status
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Payment extends \Magento\Checkout\Block\Onepage\AbstractOnepage
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->getCheckout()->setStepData(
            'payment',
            ['label' => __('Payment Information'), 'is_show' => $this->isShow()]
        );
        parent::_construct();
    }

    /**
     * Getter
     *
     * @return float
     */
    public function getQuoteBaseGrandTotal()
    {
        return (double)$this->getQuote()->getBaseGrandTotal();
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        $registerParam = $this->getRequest()->getParam('register');
        return [
            'quoteBaseGrandTotal' => $this->getQuoteBaseGrandTotal(),
            'progressUrl' => $this->getUrl('checkout/onepage/progress'),
            'reviewUrl' => $this->getUrl('checkout/onepage/review'),
            'failureUrl' => $this->getUrl('checkout/cart'),
            'getAddressUrl' => $this->getUrl('checkout/onepage/getAddress') . 'address/',
            'checkout' => [
                'suggestRegistration' => $registerParam || $registerParam === '',
                'saveUrl' => $this->getUrl('checkout/onepage/saveMethod'),
            ],
            'billing' => ['saveUrl' => $this->getUrl('checkout/onepage/saveBilling')],
            'shipping' => ['saveUrl' => $this->getUrl('checkout/onepage/saveShipping')],
            'shippingMethod' => ['saveUrl' => $this->getUrl('checkout/onepage/saveShippingMethod')],
            'payment' => [
                'defaultPaymentMethod' => $this->getChildBlock('methods')->getSelectedMethodCode(),
                'saveUrl' => $this->getUrl('checkout/onepage/savePayment'),
            ],
            'review' => [
                'saveUrl' => $this->getUrl('checkout/onepage/saveOrder'),
                'successUrl' => $this->getUrl('checkout/onepage/success'),
            ]
        ];
    }
}
