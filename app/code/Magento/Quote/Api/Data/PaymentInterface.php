<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

/**
 * Interface PaymentInterface
 * @api
 */
interface PaymentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_PO_NUMBER = 'po_number';

    const KEY_METHOD = 'method';

    const KEY_CC_OWNER = 'cc_owner';

    const KEY_CC_NUMBER = 'cc_number';

    const KEY_CC_TYPE = 'cc_type';

    const KEY_CC_EXP_YEAR = 'cc_exp_year';

    const KEY_CC_EXP_MONTH = 'cc_exp_month';

    const KEY_ADDITIONAL_DATA = 'additional_data';

    /**#@-*/

    /**
     * Get purchase order number
     *
     * @return string|null
     */
    public function getPoNumber();

    /**
     * Set purchase order number
     *
     * @param string $poNumber
     * @return $this
     */
    public function setPoNumber($poNumber);

    /**
     * Get payment method code
     *
     * @return string
     */
    public function getMethod();

    /**
     * Set payment method code
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method);

    /**
     * Get credit card owner
     *
     * @return string|null
     */
    public function getCcOwner();

    /**
     * Set credit card owner
     *
     * @param string $ccOwner
     * @return $this
     */
    public function setCcOwner($ccOwner);

    /**
     * Get credit card number
     *
     * @return string|null
     */
    public function getCcNumber();

    /**
     * Set credit card number
     *
     * @param string $ccNumber
     * @return $this
     */
    public function setCcNumber($ccNumber);

    /**
     * Get credit card type
     *
     * @return string|null
     */
    public function getCcType();

    /**
     * Set credit card type
     *
     * @param string $ccType
     * @return $this
     */
    public function setCcType($ccType);

    /**
     * Get credit card expiration year
     *
     * @return string|null
     */
    public function getCcExpYear();

    /**
     * Set credit card expiration year
     *
     * @param string $ccExpYear
     * @return $this
     */
    public function setCcExpYear($ccExpYear);

    /**
     * Get credit card expiration month
     *
     * @return string|null
     */
    public function getCcExpMonth();

    /**
     * Set credit card expiration month
     *
     * @param string $ccExpMonth
     * @return $this
     */
    public function setCcExpMonth($ccExpMonth);

    /**
     * Get payment additional details
     *
     * @return string[]|null
     */
    public function getAdditionalData();

    /**
     * Set payment additional details
     *
     * @param string $additionalData
     * @return $this
     */
    public function setAdditionalData($additionalData);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\PaymentExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\PaymentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\PaymentExtensionInterface $extensionAttributes);
}
