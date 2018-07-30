<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Serialize\Serializer;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Used to serialize sensitive data.
 */
class Sensitive implements SerializerInterface
{
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param EncryptorInterface $encryptor
     * @param SerializerInterface $serializer
     */
    public function __construct(
        EncryptorInterface $encryptor,
        SerializerInterface $serializer
    ) {
        $this->encryptor = $encryptor;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function serialize($data)
    {
        $serialized = $this->serializer->serialize($data);
        if (is_string($serialized)) {
            $serialized = $this->encryptor->encrypt($serialized);
        }

        return $serialized;
    }

    /**
     * @inheritDoc
     */
    public function unserialize($string)
    {
        $string = $this->encryptor->decrypt($string);

        return $this->serializer->unserialize($string);
    }
}
