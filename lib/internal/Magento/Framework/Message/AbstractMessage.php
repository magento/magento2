<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @var array
     */
    protected $data;

    /**
     * @param string $text
     */
    public function __construct(
        $text = null
    ) {
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
        return (string)$this->text;
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
        $out = $this->getType() . ': ' . $this->getIdentifier() . ': ' . $this->getText();
        return $out;
    }

    /**
     * Sets message data
     *
     * @param array $data
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setData(array $data = [])
    {
        array_walk_recursive(
            $data,
            function ($element) {
                if (is_object($element) && !$element instanceof \Serializable) {
                    throw new \InvalidArgumentException('Only serializable content is allowed.');
                }
            }
        );

        $this->data = $data;
        return $this;
    }

    /**
     * Returns message data
     *
     * @return array
     */
    public function getData()
    {
        return (array)$this->data;
    }
}
