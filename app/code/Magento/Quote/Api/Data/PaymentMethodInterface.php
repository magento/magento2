<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

interface PaymentMethodInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get payment method code
     *
     * @return string
     */
    public function getCode();

    /**
     * Get payment method title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\PaymentMethodExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\PaymentMethodExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Quote\Api\Data\PaymentMethodExtensionInterface $extensionAttributes
    );
}
