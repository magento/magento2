<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Unsecured;

use Magento\Framework\Jwt\HeaderInterface;
use Magento\Framework\Jwt\JwtInterface;

/**
 * Unprotected JWT.
 */
interface UnsecuredJwtInterface extends JwtInterface
{
    /**
     * Protected (not really) headers.
     *
     * Same as "[getHeader()]" for compact serialization.
     *
     * @return HeaderInterface[]
     */
    public function getProtectedHeaders(): array;

    /**
     * Unprotected header can be present when JSON serialization is employed.
     *
     * @return HeaderInterface[]|null
     */
    public function getUnprotectedHeaders(): ?array;
}
