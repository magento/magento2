<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
