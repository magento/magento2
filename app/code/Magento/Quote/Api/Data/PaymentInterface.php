<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
