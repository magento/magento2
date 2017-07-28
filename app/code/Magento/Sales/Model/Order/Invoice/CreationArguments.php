<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice;

/**
 * Creation arguments for Invoice.
 *
 * @api
 * @since 2.2.0
 */
class CreationArguments implements \Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface
{
    /**
     * @var \Magento\Sales\Api\Data\InvoiceCreationArgumentsExtensionInterface
     * @since 2.2.0
     */
    private $extensionAttributes;

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\InvoiceCreationArgumentsExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;

        return $this;
    }
}
