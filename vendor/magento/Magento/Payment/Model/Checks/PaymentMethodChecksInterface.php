<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
     */
    public function getCode();

    /**
     * Using internal pages for input payment data
     * Can be used in admin
     *
     * @return bool
     */
    public function canUseInternal();

    /**
     * Can be used in regular checkout
     *
     * @return bool
     */
    public function canUseCheckout();

    /**
     * To check billing country is allowed for the payment method
     *
     * @param string $country
     * @return bool
     */
    public function canUseForCountry($country);

    /**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode);

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null);
}
