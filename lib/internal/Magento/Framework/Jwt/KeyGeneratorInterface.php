<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt;

use Magento\Framework\Jwt\Data\Jwk;

/**
 * Abstraction to create different types of JWK.
 */
interface KeyGeneratorInterface
{
    /**
     * Creates new Jwk instance.
     *
     * @return Jwk
     */
    public function create(): Jwk;
}
