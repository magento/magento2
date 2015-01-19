<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Service\V1\Data\Cart;

/**
 * @codeCoverageIgnore
 */
class PaymentMethod extends \Magento\Framework\Api\AbstractExtensibleObject
{
    /**
     * Payment method
     */
    const METHOD = 'method';

    /**
     *  Purchase order number
     */
    const PO_NUMBER = 'po_number';

    /**
     * Credit card owner
     */
    const CC_OWNER = 'cc_owner';

    /**
     * Credit card number
     */
    const CC_NUMBER = 'cc_number';

    /**
     * Credit card type
     */
    const CC_TYPE = 'cc_type';

    /**
     * Credit card expiration year
     */
    const CC_EXP_YEAR = 'cc_exp_year';

    /**
     * Credit card expiration month
     */
    const CC_EXP_MONTH = 'cc_exp_month';

    /**
     * Additional payment details
     */
    const PAYMENT_DETAILS = 'payment_details';

    /**
     * Get purchase order number
     *
     * @return string|null
     */
    public function getPoNumber()
    {
        return $this->_get(self::PO_NUMBER);
    }

    /**
     * Get payment method code
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_get(self::METHOD);
    }

    /**
     * Get credit card owner
     *
     * @return string|null
     */
    public function getCcOwner()
    {
        return $this->_get(self::CC_OWNER);
    }

    /**
     * Get credit card number
     *
     * @return string|null
     */
    public function getCcNumber()
    {
        return $this->_get(self::CC_NUMBER);
    }

    /**
     * Get credit card type
     *
     * @return string|null
     */
    public function getCcType()
    {
        return $this->_get(self::CC_TYPE);
    }

    /**
     * Get credit card expiration year
     *
     * @return string|null
     */
    public function getCcExpYear()
    {
        return $this->_get(self::CC_EXP_YEAR);
    }

    /**
     * Get credit card expiration month
     *
     * @return string|null
     */
    public function getCcExpMonth()
    {
        return $this->_get(self::CC_EXP_MONTH);
    }

    /**
     * Get payment additional details
     *
     * @return string|null
     */
    public function getPaymentDetails()
    {
        return $this->_get(self::PAYMENT_DETAILS);
    }
}
