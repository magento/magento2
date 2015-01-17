<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
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
     * Create message instance with specified parameters
     *
     * @param string $type
     * @param string $text
     * @throws \InvalidArgumentException
     * @return MessageInterface
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
