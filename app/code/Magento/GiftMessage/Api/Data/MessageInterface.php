<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Api\Data;

interface MessageInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Returns the gift message ID.
     *
     * @return int|null Gift message ID. Otherwise, null.
     */
    public function getGiftMessageId();

    /**
     * Returns the customer ID.
     *
     * @return int|null Customer ID. Otherwise, null.
     */
    public function getCustomerId();

    /**
     * Returns the sender name.
     *
     * @return string Sender name.
     */
    public function getSender();

    /**
     * Returns the recipient name.
     *
     * @return string Recipient name.
     */
    public function getRecipient();

    /**
     * Returns the message text.
     *
     * @return string Message text.
     */
    public function getMessage();
}
