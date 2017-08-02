<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Message;

use Magento\Framework\ObjectManagerInterface;

/**
 * Message model factory
 * @since 2.0.0
 */
class Factory
{
    /**
     * Allowed message types
     *
     * @var string[]
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create a message instance of a given type with given text.
     *
     * @param string|null $type The message type to create, must correspond to a message type under the
     * namespace Magento\Framework\Message\
     * @param string $text The text to inject into the message
     * @throws \InvalidArgumentException Exception gets thrown if type does not correspond to a valid Magento message
     * @return MessageInterface
     * @since 2.0.0
     */
    public function create($type, $text = null)
    {
        if (!in_array($type, $this->types)) {
            throw new \InvalidArgumentException('Wrong message type');
        }

        $className = 'Magento\\Framework\\Message\\' . ucfirst($type);

        $message = $this->objectManager->create($className, $text === null ? [] : ['text' => $text]);
        if (!$message instanceof MessageInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement \Magento\Framework\Message\MessageInterface'
            );
        }

        return $message;
    }
}
