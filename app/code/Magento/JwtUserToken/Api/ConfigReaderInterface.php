<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Api;

/**
 * Provides JWT configurations.
 */
interface ConfigReaderInterface
{
    public const JWT_TYPE_JWS = 0;

    public const JWT_TYPE_JWE = 0;

    /**
     * Algorithm to use for authentication JWTs.
     *
     * @return string
     */
    public function getJwtAlgorithm(): string;

    /**
     * Find JWT type based on algorithm.
     *
     * @param string $algorithm
     * @return int
     */
    public function getJwtAlgorithmType(string $algorithm): int;

    /**
     * Algorithm to encrypt JWE content with.
     *
     * @return string
     */
    public function getJweContentAlgorithm(): string;

    /**
     * Customer tokens TTL in minutes.
     *
     * @return int
     */
    public function getCustomerTtl(): int;

    /**
     * Admin tokens TTL in minutes.
     *
     * @return int
     */
    public function getAdminTtl(): int;
}
