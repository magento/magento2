<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model\Data;

use Magento\Framework\Jwt\ClaimInterface;

class Claim implements ClaimInterface
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
     * @var string|null
     */
    private $class;

    /**
     * @param string $name
     * @param mixed $value
     * @param string|null $class
     */
    public function __construct(string $name, $value, ?string $class)
    {
        $this->name = $name;
        $this->value = $value;
        $this->class = $class;
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
        return $this->class;
    }

    /**
     * @inheritDoc
     */
    public function isHeaderDuplicated(): bool
    {
        return false;
    }
}
