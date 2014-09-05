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
 * @method PaymentMethod create()
 */
class PaymentMethodBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * Get purchase order number
     *
     * @param string $value
     * @return $this
     */
    public function setPoNumber($value)
    {
        return $this->_set(PaymentMethod::PO_NUMBER, $value);
    }

    /**
     * Get payment method code
     *
     * @param string $value
     * @return $this
     */
    public function setMethod($value)
    {
        return $this->_set(PaymentMethod::METHOD, $value);
    }

    /**
     * Get credit card owner
     *
     * @param string $value
     * @return $this
     */
    public function setCcOwner($value)
    {
        return $this->_set(PaymentMethod::CC_OWNER, $value);
    }

    /**
     * Get credit card number
     *
     * @param string $value
     * @return $this
     */
    public function setCcNumber($value)
    {
        return $this->_set(PaymentMethod::CC_NUMBER, $value);
    }

    /**
     * Get credit card type
     *
     * @param string $value
     * @return $this
     */
    public function setCcType($value)
    {
        return $this->_set(PaymentMethod::CC_TYPE, $value);
    }

    /**
     * Get credit card expiration year
     *
     * @param string $value
     * @return $this
     */
    public function setCcExpYear($value)
    {
        return $this->_set(PaymentMethod::CC_EXP_YEAR, $value);
    }

    /**
     * Get credit card expiration month
     *
     * @param string $value
     * @return $this
     */
    public function setCcExpMonth($value)
    {
        return $this->_set(PaymentMethod::CC_EXP_MONTH, $value);
    }

    /**
     * Set payment additional payment details
     *
     * @param string $value
     * @return $this
     */
    public function setPaymentDetails($value)
    {
        return $this->_set(PaymentMethod::PAYMENT_DETAILS, $value);
    }
}
