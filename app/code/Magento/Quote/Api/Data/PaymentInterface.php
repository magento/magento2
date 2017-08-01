<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

/**
 * Interface PaymentInterface
 * @api
 * @since 2.0.0
 */
interface PaymentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_PO_NUMBER = 'po_number';

    const KEY_METHOD = 'method';

    const KEY_ADDITIONAL_DATA = 'additional_data';

    /**#@-*/

    /**
     * Get purchase order number
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getPoNumber();

    /**
     * Set purchase order number
     *
     * @param string $poNumber
     * @return $this
     * @since 2.0.0
     */
    public function setPoNumber($poNumber);

    /**
     * Get payment method code
     *
     * @return string
     * @since 2.0.0
     */
    public function getMethod();

    /**
     * Set payment method code
     *
     * @param string $method
     * @return $this
     * @since 2.0.0
     */
    public function setMethod($method);

    /**
     * Get payment additional details
     *
     * @return string[]|null
     * @since 2.0.0
     */
    public function getAdditionalData();

    /**
     * Set payment additional details
     *
     * @param string $additionalData
     * @return $this
     * @since 2.0.0
     */
    public function setAdditionalData($additionalData);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\PaymentExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\PaymentExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\PaymentExtensionInterface $extensionAttributes);
}
