<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Jwt\ClaimInterface;
use Magento\Framework\Jwt\HeaderParameterInterface;

/**
 * Generates JWT strings.
 */
interface JwtGeneratorInterface
{
    /**
     * Generate JWT based on given data.
     *
     * @param HeaderParameterInterface[] $protectedHeaders
     * @param HeaderParameterInterface[] $publicHeaders
     * @param ClaimInterface[] $claims
     * @param UserContextInterface $userContext For user context.
     * @return string
     */
    public function generate(
        array $protectedHeaders,
        array $publicHeaders,
        array $claims,
        UserContextInterface $userContext
    ): string;
}
