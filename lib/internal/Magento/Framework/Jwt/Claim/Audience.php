<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Claim;

use Magento\Framework\Jwt\ClaimInterface;

/**
 * "aud" claim.
 */
class Audience implements ClaimInterface
{
    /**
     * @var string[]
     */
    private $value;

    /**
     * @var bool
     */
    private $duplicate;

    /**
     * @param string[] $value
     * @param bool $duplicate
     */
    public function __construct(array $value, bool $duplicate = false)
    {
        if (!$value) {
            throw new \InvalidArgumentException("Audience list cannot be empty");
        }
        $this->value = $value;
        $this->duplicate = $duplicate;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'aud';
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return json_encode($this->value);
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
