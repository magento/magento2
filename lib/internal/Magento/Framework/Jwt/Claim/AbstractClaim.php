<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Claim;

use Magento\Framework\Jwt\ClaimInterface;

/**
 * Abstract user-defined claim.
 */
abstract class AbstractClaim implements ClaimInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int|float|string|bool|array|null
     */
    private $value;

    /**
     * @var int|null
     */
    private $class;

    /**
     * @var bool
     */
    private $duplicated;

    /**
     * Parse NumericDate and return DateTime with UTC timezone.
     *
     * @param string $date
     * @return \DateTimeInterface
     */
    public static function parseNumericDate(string $date): \DateTimeInterface
    {
        $dt = \DateTime::createFromFormat('Y-m-d\TH:i:sT', $date);
        $dt->setTimezone(new \DateTimeZone('UTC'));

        return \DateTimeImmutable::createFromMutable($dt);
    }

    public function __construct(string $name, $value, ?int $class, bool $duplicated = false)
    {
        $this->name = $name;
        $this->value = $value;
        $this->class = $class;
        $this->duplicated = $duplicated;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
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
        return $this->class;
    }

    /**
     * @inheritDoc
     */
    public function isHeaderDuplicated(): bool
    {
        return $this->duplicated;
    }
}
