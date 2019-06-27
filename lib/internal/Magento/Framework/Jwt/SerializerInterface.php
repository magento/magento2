<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt;

use Magento\Framework\Jwt\Data\Jwt;

/**
 * Provides and abstraction for JWT serialization/unserialization.
 */
interface SerializerInterface
{
    /**
     * Converts JWT to a string representation.
     *
     * @param Jwt $jwt
     * @param int|null $signatureIndex
     * @return string
     * @throws \LogicException
     */
    public function serialize(Jwt $jwt, ?int $signatureIndex = null): string;

    /**
     * Converts string token to JWT representation.
     *
     * @param string $token
     * @return Jwt
     * @throws \InvalidArgumentException
     */
    public function unserialize(string $token): Jwt;
}
