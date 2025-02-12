<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
