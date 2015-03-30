<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Message;

use Magento\Framework\ObjectManagerInterface;

/**
 * Message model factory
 */
class Factory
{
    /**
     * Allowed message types
     *
     * @var string[]
     */
    protected $types = [
        MessageInterface::TYPE_ERROR,
        MessageInterface::TYPE_WARNING,
        MessageInterface::TYPE_NOTICE,
        MessageInterface::TYPE_SUCCESS,
    ];

    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create a message instance of a given type with given text.
     *
     * @param string $type The message type to create, must implement \Magento\Framework\Message\MessageInterface
     * @param string $text The text to inject into the message
     * @throws \InvalidArgumentException Exception gets thrown if given type does not implement MessageInterface
     * @return MessageInterface
     *
     * @api
     */
    public function create($type, $text)
    {
        if (!in_array($type, $this->types)) {
            throw new \InvalidArgumentException('Wrong message type');
        }

        $className = 'Magento\\Framework\\Message\\' . ucfirst($type);
        $message = $this->objectManager->create($className, ['text' => $text]);
        if (!$message instanceof MessageInterface) {
            throw new \InvalidArgumentException($className . ' doesn\'t implement \Magento\Framework\Message\MessageInterface');
        }

        return $message;
    }
}
