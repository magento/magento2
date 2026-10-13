<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Unsecured;

use Magento\Framework\Jwt\EncryptionSettingsInterface;

/**
 * No encryption.
 */
class NoEncryption implements EncryptionSettingsInterface
{
    /**
     * @inheritDoc
     */
    public function getAlgorithmName(): string
    {
        return 'none';
    }
}
