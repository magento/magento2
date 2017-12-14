<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Track URL Creation interface.
 *
 * @api
 */
interface TrackUrlCreationInterface extends TrackInterface, ExtensibleDataInterface
{
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\TrackUrlCreationExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\TrackUrlCreationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\TrackUrlCreationExtensionInterface $extensionAttributes
    );
}
