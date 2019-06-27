<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt\ClaimChecker;

use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Jwt\ClaimCheckerInterface;
use Magento\Framework\Jwt\InvalidClaimException;

/**
 * Checks claim expiration time.
 */
class ExpirationTime implements ClaimCheckerInterface
{
    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @param DateTimeFactory $dateTimeFactory
     */
    public function __construct(DateTimeFactory $dateTimeFactory)
    {
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @inheritdoc
     */
    public function checkClaim($value): void
    {
        $now = $this->dateTimeFactory->create('now', new \DateTimeZone('UTC'));

        if ($now->getTimestamp() > (int) $value) {
            throw new InvalidClaimException('Token is expired.', $this->supportedClaim(), $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function supportedClaim(): string
    {
        return 'exp';
    }
}
