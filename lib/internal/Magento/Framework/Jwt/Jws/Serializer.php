<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt\Jws;

use Jose\Component\Signature\Serializer\CompactSerializer;
use Magento\Framework\Jwt\Data\Jwt;
use Magento\Framework\Jwt\SerializerInterface;

/**
 * Provides a JWS implementation for JWT serializer interface.
 */
class Serializer implements SerializerInterface
{
    /**
     * @var CompactSerializer
     */
    private $serializer;

    /**
     * @param CompactSerializer $serializer
     */
    public function __construct(CompactSerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function serialize(Jwt $jwt, ?int $signatureIndex = null): string
    {
        return $this->serializer->serialize($jwt->getToken(), $signatureIndex);
    }

    /**
     * @inheritdoc
     */
    public function unserialize(string $token): Jwt
    {
        return new Jwt($this->serializer->unserialize($token));
    }
}
