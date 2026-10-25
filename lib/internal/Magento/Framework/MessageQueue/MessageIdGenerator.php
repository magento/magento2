<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Generate unique id for queue message.
 */
class MessageIdGenerator implements MessageIdGeneratorInterface
{
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->encryptor = $encryptor;
    }

    /**
     * @inheritdoc
     */
    public function generate($topicName)
    {
        return $this->encryptor->hash(uniqid($topicName, true));
    }
}
