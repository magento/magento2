<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Composer;

use Composer\IO\BufferIO;

/**
 * Class creates BufferIO instance
 */
class BufferIoFactory
{
    /**
     * Creates BufferIO instance
     *
     * @return BufferIO
     */
    public function create()
    {
        return new BufferIO();
    }
}
