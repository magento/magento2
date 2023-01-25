<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model\Data;

use Magento\Framework\Jwt\HeaderInterface;
use Magento\Framework\Jwt\Payload\ClaimsPayloadInterface;
use Magento\JwtUserToken\Api\Data\JwtTokenDataInterface;

class JwtTokenData implements JwtTokenDataInterface
{
    /**
     * @var \DateTimeImmutable
     */
    private $issued;

    /**
     * @var \DateTimeImmutable
     */
    private $expires;

    /**
     * @var HeaderInterface
     */
    private $jwtHeader;

    /**
     * @var ClaimsPayloadInterface
     */
    private $jwtClaims;

    /**
     * @param \DateTimeImmutable $issued
     * @param \DateTimeImmutable $expires
     * @param HeaderInterface $jwtHeader
     * @param ClaimsPayloadInterface $jwtClaims
     */
    public function __construct(
        \DateTimeImmutable $issued,
        \DateTimeImmutable $expires,
        HeaderInterface $jwtHeader,
        ClaimsPayloadInterface $jwtClaims
    ) {
        $this->issued = $issued;
        $this->expires = $expires;
        $this->jwtHeader = $jwtHeader;
        $this->jwtClaims = $jwtClaims;
    }

    public function getIssued(): \DateTimeImmutable
    {
        return $this->issued;
    }

    public function getExpires(): \DateTimeImmutable
    {
        return $this->expires;
    }

    public function getJwtHeader(): HeaderInterface
    {
        return $this->jwtHeader;
    }

    public function getJwtClaims(): ClaimsPayloadInterface
    {
        return $this->jwtClaims;
    }
}
