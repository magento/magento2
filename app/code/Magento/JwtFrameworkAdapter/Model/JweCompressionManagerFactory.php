<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
