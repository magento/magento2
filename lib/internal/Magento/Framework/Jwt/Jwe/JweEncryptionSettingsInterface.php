<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Jwe;

use Magento\Framework\Jwt\EncryptionSettingsInterface;

/**
 * JWE Encryption settings.
 */
interface JweEncryptionSettingsInterface extends EncryptionSettingsInterface
{
    public const CONTENT_ENCRYPTION_ALGO_A128_HS256 = 'A128CBC-HS256';

    public const CONTENT_ENCRYPTION_ALGO_A192_HS384 = 'A192CBC-HS384';

    public const CONTENT_ENCRYPTION_ALGO_A256_HS512 = 'A256CBC-HS512';

    public const CONTENT_ENCRYPTION_ALGO_A128GCM = 'A128GCM';

    public const CONTENT_ENCRYPTION_ALGO_A192GCM = 'A192GCM';

    public const CONTENT_ENCRYPTION_ALGO_A256GCM = 'A256GCM';

    /**
     * Algorithm used to encrypt payload.
     *
     * "enc" header value.
     *
     * @return string
     */
    public function getContentEncryptionAlgorithm(): string;
}
