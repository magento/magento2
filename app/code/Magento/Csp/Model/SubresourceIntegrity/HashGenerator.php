<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\SubresourceIntegrity;

/**
 * Subresource Integrity hashes generator.
 */
class HashGenerator
{
    /**
     * CHashing algorithm.
     *
     * @var string
     */
    private const ALGORITHM = 'sha256';

    /**
     * Computes integrity hash for a given content.
     *
     * @param string $content
     *
     * @return string
     */
    public function generate(string $content): string
    {
        $base64Hash = base64_encode(
            hash(self::ALGORITHM, $content, true)
        );

        return self::ALGORITHM . "-{$base64Hash}";
    }
}
