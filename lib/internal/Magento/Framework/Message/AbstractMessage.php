<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message;

/**
 * Abstract message model
 *
 * @api
 * @since 2.0.0
 */
abstract class AbstractMessage implements MessageInterface
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $text;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $identifier;

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $isSticky = false;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $data;

    /**
     * @param string $text
     * @since 2.0.0
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
     * @since 2.0.0
     */
    abstract public function getType();

    /**
     * Getter for text of message
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getIsSticky()
    {
        return $this->isSticky;
    }

    /**
     * Retrieve message as a string
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getData()
    {
        return (array)$this->data;
    }
}
