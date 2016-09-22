<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Api\Data;

/**
 * Interface MessageInterface
 * @api
 */
interface MessageInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const GIFT_MESSAGE_ID = 'gift_message_id';
    const CUSTOMER_ID = 'customer_id';
    const SENDER = 'sender';
    const RECIPIENT = 'recipient';
    const MESSAGE = 'message';
    /**#@-*/

    /**
     * Return the gift message ID.
     *
     * @return int|null Gift message ID. Otherwise, null.
     */
    public function getGiftMessageId();

    /**
     * Set the gift message ID.
     *
     * @param int|null $id
     * @return $this
     */
    public function setGiftMessageId($id);

    /**
     * Return the customer ID.
     *
     * @return int|null Customer ID. Otherwise, null.
     */
    public function getCustomerId();

    /**
     * Set the customer ID.
     *
     * @param int|null $id
     * @return $this
     */
    public function setCustomerId($id);

    /**
     * Return the sender name.
     *
     * @return string Sender name.
     */
    public function getSender();

    /**
     * Set the sender name.
     *
     * @param string $sender
     * @return $this
     */
    public function setSender($sender);

    /**
     * Return the recipient name.
     *
     * @return string Recipient name.
     */
    public function getRecipient();

    /**
     * Get the recipient name.
     *
     * @param string $recipient
     * @return $this
     */
    public function setRecipient($recipient);

    /**
     * Return the message text.
     *
     * @return string Message text.
     */
    public function getMessage();

    /**
     * Set the message text.
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\GiftMessage\Api\Data\MessageExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\GiftMessage\Api\Data\MessageExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\GiftMessage\Api\Data\MessageExtensionInterface $extensionAttributes
    );
}
