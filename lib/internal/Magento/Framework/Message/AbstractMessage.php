<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message;

/**
 * Abstract message model
 */
abstract class AbstractMessage implements MessageInterface
{
    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var bool
     */
    protected $isSticky = false;

    /**
     * @param string $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    /**
     * Getter message type
     *
     * @return string
     */
    abstract public function getType();

    /**
     * Getter for text of message
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Setter message text
     *
     * @param string $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Setter message identifier
     *
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Getter message identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Setter for flag. Whether message is sticky
     *
     * @param bool $isSticky
     * @return $this
     */
    public function setIsSticky($isSticky = true)
    {
        $this->isSticky = $isSticky;
        return $this;
    }

    /**
     * Getter for flag. Whether message is sticky
     *
     * @return bool
     */
    public function getIsSticky()
    {
        return $this->isSticky;
    }

    /**
     * Retrieve message as a string
     *
     * @return string
     */
    public function toString()
    {
        $out = $this->getType() . ': ' . $this->getText();
        return $out;
    }
}
