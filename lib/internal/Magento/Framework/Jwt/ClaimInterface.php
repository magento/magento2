<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt;

/**
 * JWT Claim.
 */
interface ClaimInterface
{
    public const CLASS_REGISTERED = 1;

    public const CLASS_PUBLIC = 2;

    public const CLASS_PRIVATE = 3;

    /**
     * Claim name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Value carried.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Claim class when possible to identify.
     *
     * @return int|null
     */
    public function getClass(): ?int;

    /**
     * Whether to duplicate this claim to JOSE header.
     *
     * Only works for JWEs.
     *
     * @return bool
     */
    public function isHeaderDuplicated(): bool;
}
