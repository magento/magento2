<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt;

/**
 * Encryption settings for JWT.
 */
interface EncryptionSettingsInterface
{
    /**
     * Algorithm name.
     *
     * @return string
     */
    public function getAlgorithmName(): string;
}
