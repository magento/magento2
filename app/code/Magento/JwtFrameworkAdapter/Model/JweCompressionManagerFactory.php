<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Encryption\Compression\CompressionMethodManager;

class JweCompressionManagerFactory
{
    /**
     * @var \Jose\Component\Encryption\Compression\CompressionMethod[]
     */
    private $methods;

    /**
     * @param \Jose\Component\Encryption\Compression\CompressionMethod[] $methods
     */
    public function __construct(array $methods)
    {
        $this->methods = $methods;
    }

    public function create(): CompressionMethodManager
    {
        return new CompressionMethodManager($this->methods);
    }
}
