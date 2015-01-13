<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Service\V1\Data;

/**
 * Gift message data object.
 *
 * @codeCoverageIgnore
 */
class Message extends \Magento\Framework\Api\AbstractExtensibleObject
{
    /**
     * Gift message ID.
     */
    const GIFT_MESSAGE_ID = 'gift_message_id';

    /**
     * Sender name.
     */
    const SENDER = 'sender';

    /**
     * Recipient name.
     */
    const RECIPIENT = 'recipient';

    /**
     * Message text.
     */
    const MESSAGE = 'message';

    /**
     * Customer ID.
     */
    const CUSTOMER_ID = 'customer_id';

    /**
     * Returns the gift message ID.
     *
     * @return int|null Gift message ID. Otherwise, null.
     */
    public function getGiftMessageId()
    {
        return $this->_get(self::GIFT_MESSAGE_ID);
    }

    /**
     * Returns the customer ID.
     *
     * @return int|null Customer ID. Otherwise, null.
     */
    public function getCustomerId()
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * Returns the sender name.
     *
     * @return string Sender name.
     */
    public function getSender()
    {
        return $this->_get(self::SENDER);
    }

    /**
     * Returns the recipient name.
     *
     * @return string Recipient name.
     */
    public function getRecipient()
    {
        return $this->_get(self::RECIPIENT);
    }

    /**
     * Returns the message text.
     *
     * @return string Message text.
     */
    public function getMessage()
    {
        return $this->_get(self::MESSAGE);
    }
}
