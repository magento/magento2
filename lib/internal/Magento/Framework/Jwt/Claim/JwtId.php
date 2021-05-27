<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Claim;

use Magento\Framework\Jwt\ClaimInterface;

/**
 * "jti" claim.
 */
class JwtId implements ClaimInterface
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
     * @param string|null $value
     * @param bool $duplicate
     */
    public function __construct(?string $value = null, bool $duplicate = false)
    {
        $this->value = $value ?? $this->generateRandom();
        $this->duplicate = $duplicate;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'jti';
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

    private function generateRandom(): string
    {
        return implode('', array_map(
            function($value) {
                return chr($value);
            },
            array_map(
                function() {
                    return random_int(33, 126);
                },
                array_fill(0, 21, null)
            )
        ));
    }
}
