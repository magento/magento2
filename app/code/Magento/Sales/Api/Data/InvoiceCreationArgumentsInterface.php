<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface for creation arguments for Invoice.
 *
 * @api
 */
interface InvoiceCreationArgumentsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Gets existing extension attributes.
     *
     * @return \Magento\Sales\Api\Data\InvoiceCreationArgumentsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Sets extension attributes.
     *
     * @param \Magento\Sales\Api\Data\InvoiceCreationArgumentsExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\InvoiceCreationArgumentsExtensionInterface $extensionAttributes
    );
}
