<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Jwt\Jwe\JweEncryptionSettingsInterface;

/**
 * JWE content encryption algorithm options
 */
class JweAlgorithmSource implements OptionSourceInterface
{
    private const ALGS = [
        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM,
        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A192GCM,
        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A256GCM,
        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256,
        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A192_HS384,
        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A256_HS512
    ];

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $options = [];
        foreach (self::ALGS as $algorithm) {
            $options[] = ['label' => __($algorithm), 'value' => $algorithm];
        }

        return $options;
    }
}
