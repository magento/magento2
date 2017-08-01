<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Input argument for invoice creation
 *
 * Interface InvoiceItemCreationInterface
 *
 * @api
 * @since 2.2.0
 */
interface InvoiceItemCreationInterface extends LineItemInterface, ExtensibleDataInterface
{
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\InvoiceItemCreationExtensionInterface|null
     * @since 2.2.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\InvoiceItemCreationExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.2.0
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\InvoiceItemCreationExtensionInterface $extensionAttributes
    );
}
