<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\Compression\Deflate;

class JweCompressionManagerFactory
{
    public function create(): CompressionMethodManager
    {
        return new CompressionMethodManager([new Deflate()]);
    }
}
