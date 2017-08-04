<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface for creation arguments for Invoice.
 *
 * @api
 * @since 2.1.2
 */
interface InvoiceCreationArgumentsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Gets existing extension attributes.
     *
     * @return \Magento\Sales\Api\Data\InvoiceCreationArgumentsExtensionInterface|null
     * @since 2.1.2
     */
    public function getExtensionAttributes();

    /**
     * Sets extension attributes.
     *
     * @param \Magento\Sales\Api\Data\InvoiceCreationArgumentsExtensionInterface $extensionAttributes
     *
     * @return $this
     * @since 2.1.2
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\InvoiceCreationArgumentsExtensionInterface $extensionAttributes
    );
}
