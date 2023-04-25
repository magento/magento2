<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Claim;

use Magento\Framework\Jwt\ClaimInterface;

/**
 * "sub" claim.
 */
class Subject implements ClaimInterface
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
     * @param string $value
     * @param bool $duplicate
     */
    public function __construct(string $value, bool $duplicate = false)
    {
        $this->value = $value;
        $this->duplicate = $duplicate;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'sub';
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
