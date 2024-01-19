<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Jws;

use Magento\Framework\Jwt\HeaderInterface;
use Magento\Framework\Jwt\JwtInterface;
use Magento\Framework\Jwt\PayloadInterface;

/**
 * Abstract JWS DTO.
 */
abstract class AbstractJws implements JwtInterface
{
    /**
     * @var HeaderInterface[]
     */
    private $protectedHeaders;

    /**
     * @var HeaderInterface[]|null
     */
    private $unprotectedHeaders;

    /**
     * @var PayloadInterface
     */
    private $payload;

    /**
     * @param HeaderInterface[] $protectedHeaders
     * @param PayloadInterface $payload
     * @param HeaderInterface[]|null $unprotectedHeaders
     */
    public function __construct(array $protectedHeaders, PayloadInterface $payload, ?array $unprotectedHeaders)
    {
        if (!$protectedHeaders) {
            throw new \InvalidArgumentException('Need at least 1 header');
        }
        $this->protectedHeaders = array_values($protectedHeaders);
        $this->payload = $payload;
        if (!$unprotectedHeaders) {
            $unprotectedHeaders = null;
        } elseif (count($protectedHeaders) !== count($unprotectedHeaders)) {
            throw new \InvalidArgumentException('There has to be equal amount of protected and unprotected headers');
        } else {
            $unprotectedHeaders = array_values($unprotectedHeaders);
        }
        $this->unprotectedHeaders = $unprotectedHeaders;
    }

    /**
     * @inheritDoc
     */
    public function getProtectedHeaders(): array
    {
        return $this->protectedHeaders;
    }

    /**
     * @inheritDoc
     */
    public function getUnprotectedHeaders(): ?array
    {
        return $this->unprotectedHeaders;
    }

    /**
     * @inheritDoc
     */
    public function getHeader(): HeaderInterface
    {
        return $this->protectedHeaders[0];
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): PayloadInterface
    {
        return $this->payload;
    }
}
