<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

/**
 * From Address interface
 */
interface FromAddressInterface extends MessageInterface
{
    /**
     * Set from message
     *
     * @param string $fromAddress
     * @param string|null $fromName
     * @return $this
     */
    public function setFromAddress($fromAddress, $fromName = null);
}
