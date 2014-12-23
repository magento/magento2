<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GiftMessage\Api\Data;

/**
 * @see \Magento\GiftMessage\Service\V1\Data\Message
 */
interface MessageInterface
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
