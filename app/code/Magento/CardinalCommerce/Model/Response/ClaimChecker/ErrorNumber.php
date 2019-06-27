<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CardinalCommerce\Model\Response\ClaimChecker;

use Magento\Framework\Jwt\ClaimCheckerInterface;
use Magento\Framework\Jwt\InvalidClaimException;

/**
 * Checks application error number.
 *
 * A non-zero value represents the error encountered while attempting the process the message request.
 */
class ErrorNumber implements ClaimCheckerInterface
{
    /**
     * @inheritdoc
     */
    public function checkClaim($value): void
    {
        if ((int) $value !== 0) {
            throw new InvalidClaimException('Invalid Error Number.', $this->supportedClaim(), $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function supportedClaim(): string
    {
        return 'ErrorNumber';
    }
}
