<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Claim;

use Magento\Framework\Jwt\ClaimInterface;

class PublicClaim implements ClaimInterface
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
     * @param string|null $prefix
     * @param bool $headerDuplicated
     */
    public function __construct(string $name, $value, ?string $prefix, bool $headerDuplicated = false)
    {
        $this->name = $prefix ? $prefix .'-' .$name : $name;
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
        return self::CLASS_PUBLIC;
    }

    /**
     * @inheritDoc
     */
    public function isHeaderDuplicated(): bool
    {
        return $this->headerDuplicated;
    }
}
