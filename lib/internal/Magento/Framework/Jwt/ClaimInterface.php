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
    public const CLASS_REGISTERED = 'registered';

    public const CLASS_PUBLIC = 'public';

    public const CLASS_PRIVATE = 'private';

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
     * @return string|null
     */
    public function getClass(): ?string;

    /**
     * Whether to duplicate this claim to JOSE header.
     *
     * Only works for JWEs.
     *
     * @return bool
     */
    public function isHeaderDuplicated(): bool;
}
