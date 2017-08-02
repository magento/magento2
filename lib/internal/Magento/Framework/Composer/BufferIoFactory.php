<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Composer;

use Composer\IO\BufferIO;

/**
 * Class creates BufferIO instance
 * @since 2.0.0
 */
class BufferIoFactory
{
    /**
     * Creates BufferIO instance
     *
     * @return BufferIO
     * @since 2.0.0
     */
    public function create()
    {
        return new BufferIO();
    }
}
