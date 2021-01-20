<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt;

/**
 * JWT
 */
interface JwtInterface
{
    /**
     * Header.
     *
     * @return HeaderInterface
     */
    public function getHeader(): HeaderInterface;

    /**
     * Payload.
     *
     * @return PayloadInterface
     */
    public function getPayload(): PayloadInterface;
}
