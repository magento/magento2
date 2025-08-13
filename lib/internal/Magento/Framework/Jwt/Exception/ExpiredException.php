<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Exception;

/**
 * Thrown when token has expired or is not active yet.
 */
class ExpiredException extends JwtException
{
    /**
     * @var int|null
     */
    private $activeFrom;

    /**
     * @var int|null
     */
    private $expiresAt;

    /**
     * @var int
     */
    private $checked;

    public function __construct(
        ?int $expiresAt = null,
        ?int $activeFrom = null,
        $message = "JWT has expired/not active yet",
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        if (!$expiresAt && !$activeFrom) {
            throw new \InvalidArgumentException('Provide either expire time or active from time.');
        }
        $this->expiresAt = $expiresAt;
        $this->activeFrom = $activeFrom;
        $this->checked = time();
    }

    /**
     * JWT will be active from.
     *
     * @return int|null
     */
    public function getActiveFrom(): ?int
    {
        return $this->activeFrom;
    }

    /**
     * JWT expired at.
     *
     * @return int|null
     */
    public function getExpiresAt(): ?int
    {
        return $this->expiresAt;
    }

    /**
     * Check was performed at.
     *
     * @return int
     */
    public function getChecked(): int
    {
        return $this->checked;
    }
}
