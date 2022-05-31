<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model\Data;

use Magento\Framework\Jwt\ClaimInterface;
use Magento\Framework\Jwt\HeaderParameterInterface;

/**
 * User token parameters for JWTs.
 */
class JwtTokenParameters
{
    /**
     * @var HeaderParameterInterface[]
     */
    private $protectedHeaderParameters = [];

    /**
     * @var HeaderParameterInterface[]
     */
    private $publicHeaderParameters = [];

    /**
     * @var ClaimInterface[]
     */
    private $claims = [];

    /**
     * @return HeaderParameterInterface[]
     */
    public function getProtectedHeaderParameters(): array
    {
        return $this->protectedHeaderParameters;
    }

    /**
     * @param HeaderParameterInterface[] $protectedHeaderParameters
     */
    public function setProtectedHeaderParameters(array $protectedHeaderParameters): void
    {
        $this->protectedHeaderParameters = $protectedHeaderParameters;
    }

    /**
     * @return HeaderParameterInterface[]
     */
    public function getPublicHeaderParameters(): array
    {
        return $this->publicHeaderParameters;
    }

    /**
     * @param HeaderParameterInterface[] $publicHeaderParameters
     */
    public function setPublicHeaderParameters(array $publicHeaderParameters): void
    {
        $this->publicHeaderParameters = $publicHeaderParameters;
    }

    /**
     * @return ClaimInterface[]
     */
    public function getClaims(): array
    {
        return $this->claims;
    }

    /**
     * @param ClaimInterface[] $claims
     */
    public function setClaims(array $claims): void
    {
        $this->claims = $claims;
    }
}
