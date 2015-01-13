<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message;

/**
 * Interface for message
 */
interface MessageInterface
{
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
     */
    public function getType();

    /**
     * Getter for text of message
     *
     * @return string
     */
    public function getText();

    /**
     * Setter message text
     *
     * @param string $text
     * @return $this
     */
    public function setText($text);

    /**
     * Setter message identifier
     *
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier($identifier);

    /**
     * Getter message identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Setter for flag. Whether message is sticky
     *
     * @param bool $isSticky
     * @return $this
     */
    public function setIsSticky($isSticky);

    /**
     * Getter for flag. Whether message is sticky
     *
     * @return bool
     */
    public function getIsSticky();

    /**
     * Retrieve message as a string
     *
     * @return string
     */
    public function toString();
}
