<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface CreditmemoItemCreationInterface
 * @api
 * @since 100.1.3
 */
interface CreditmemoItemCreationInterface extends LineItemInterface, ExtensibleDataInterface
{
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\CreditmemoItemCreationExtensionInterface|null
     * @since 100.1.3
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoItemCreationExtensionInterface $extensionAttributes
     * @return $this
     * @since 100.1.3
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\CreditmemoItemCreationExtensionInterface $extensionAttributes
    );
}
