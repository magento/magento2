<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Claim;

use Magento\Framework\Jwt\ClaimInterface;

/**
 * "nbf" claim.
 */
class NotBefore implements ClaimInterface
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var bool
     */
    private $duplicate;

    /**
     * @param \DateTimeInterface $value
     * @param bool $duplicate
     */
    public function __construct(\DateTimeInterface $value, bool $duplicate = false)
    {
        if ($value instanceof \DateTimeImmutable) {
            $value = \DateTime::createFromImmutable($value);
        }
        $value->setTimezone(new \DateTimeZone('UTC'));
        $this->value = $value->format('Y-m-d\TH:i:s\Z UTC');
        $this->duplicate = $duplicate;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'nbf';
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function getClass(): ?int
    {
        return self::CLASS_REGISTERED;
    }

    /**
     * @inheritDoc
     */
    public function isHeaderDuplicated(): bool
    {
        return $this->duplicate;
    }
}
