<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Checks;

/**
 * Payment method interface for specification checks
 */
interface PaymentMethodChecksInterface
{
    /**
     * Retrieve payment method code
     *
     * @return string
     * @api
     */
    public function getCode();

    /**
     * Using internal pages for input payment data
     * Can be used in admin
     *
     * @return bool
     * @api
     */
    public function canUseInternal();

    /**
     * Can be used in regular checkout
     *
     * @return bool
     * @api
     */
    public function canUseCheckout();

    /**
     * To check billing country is allowed for the payment method
     *
     * @param string $country
     * @return bool
     * @api
     */
    public function canUseForCountry($country);

    /**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return bool
     * @api
     */
    public function canUseForCurrency($currencyCode);

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     * @api
     */
    public function getConfigData($field, $storeId = null);
}
