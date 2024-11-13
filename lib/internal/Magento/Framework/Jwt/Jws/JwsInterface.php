<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Jws;

use Magento\Framework\Jwt\HeaderInterface;
use Magento\Framework\Jwt\JwtInterface;

/**
 * JWS
 */
interface JwsInterface extends JwtInterface
{
    /**
     * Protected header.
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
