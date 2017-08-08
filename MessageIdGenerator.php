<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Generate unique id for queue message.
 * @since 2.2.0
 */
class MessageIdGenerator implements MessageIdGeneratorInterface
{
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     * @since 2.2.0
     */
    private $encryptor;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->encryptor = $encryptor;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function generate($topicName)
    {
        return $this->encryptor->hash(uniqid($topicName));
    }
}
