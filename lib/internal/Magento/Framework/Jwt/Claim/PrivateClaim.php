<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Claim;

use Magento\Framework\Jwt\ClaimInterface;

/**
 * Private non-registered claim.
 */
class PrivateClaim implements ClaimInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var bool
     */
    private $headerDuplicated;

    /**
     * @param string $name
     * @param mixed $value
     * @param bool $headerDuplicated
     */
    public function __construct(string $name, $value, bool $headerDuplicated = false)
    {
        $this->name = $name;
        $this->value = $value;
        $this->headerDuplicated = $headerDuplicated;
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
    public function getClass(): ?string
    {
        return self::CLASS_PRIVATE;
    }

    /**
     * @inheritDoc
     */
    public function isHeaderDuplicated(): bool
    {
        return $this->headerDuplicated;
    }
}
