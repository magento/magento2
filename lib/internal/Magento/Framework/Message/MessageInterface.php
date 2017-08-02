<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message;

/**
 * Represent a message with a type, content text, and an isSticky attribute to prevent message from being cleared.
 *
 * @api
 * @since 2.0.0
 */
interface MessageInterface
{
    /**
     * Default identifier
     */
    const DEFAULT_IDENTIFIER = 'default_message_identifier';

    /**
     * Error type
     */
    const TYPE_ERROR = 'error';

    /**
     * Warning type
     */
    const TYPE_WARNING = 'warning';

    /**
     * Notice type
     */
    const TYPE_NOTICE = 'notice';

    /**
     * Success type
     */
    const TYPE_SUCCESS = 'success';

    /**
     * Getter message type
     *
     * @return string
     * @since 2.0.0
     */
    public function getType();

    /**
     * Getter for text of message
     *
     * @return string
     * @since 2.0.0
     */
    public function getText();

    /**
     * Setter message text
     *
     * @param string $text
     * @return $this
     * @since 2.0.0
     */
    public function setText($text);

    /**
     * Setter message identifier
     *
     * @param string $identifier
     * @return $this
     * @since 2.0.0
     */
    public function setIdentifier($identifier);

    /**
     * Getter message identifier
     *
     * @return string
     * @since 2.0.0
     */
    public function getIdentifier();

    /**
     * Setter for flag. Whether message is sticky
     *
     * @param bool $isSticky
     * @return $this
     * @since 2.0.0
     */
    public function setIsSticky($isSticky);

    /**
     * Getter for flag. Whether message is sticky
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getIsSticky();

    /**
     * Retrieve message as a string
     *
     * @return string
     * @since 2.0.0
     */
    public function toString();

    /**
     * Sets message data
     *
     * @param array $data
     * @return $this
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function setData(array $data = []);

    /**
     * Returns message data
     *
     * @return array
     * @since 2.0.0
     */
    public function getData();
}
