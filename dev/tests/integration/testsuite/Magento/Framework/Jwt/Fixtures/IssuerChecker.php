<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt\Fixtures;

use Magento\Framework\Jwt\ClaimCheckerInterface;
use Magento\Framework\Jwt\InvalidClaimException;

/**
 * Checks issuer claim.
 */
class IssuerChecker implements ClaimCheckerInterface
{
    private static $claims = ['dev', 'test'];

    /**
     * @inheritdoc
     */
    public function checkClaim($value): void
    {
        if (!in_array($value, self::$claims)) {
            throw new InvalidClaimException('Claim is not supported', 'iss', $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function supportedClaim(): string
    {
        return 'iss';
    }
}
