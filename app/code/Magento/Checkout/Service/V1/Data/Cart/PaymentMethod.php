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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Checkout\Service\V1\Data\Cart;

/**
 * @codeCoverageIgnore
 */
class PaymentMethod extends \Magento\Framework\Service\Data\AbstractExtensibleObject
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
