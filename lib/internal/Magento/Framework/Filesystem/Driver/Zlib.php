<?php
/**
 * Magento filesystem zlib driver
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Driver;

/**
 * Class \Magento\Framework\Filesystem\Driver\Zlib
 *
 * @since 2.0.0
 */
class Zlib extends File
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $scheme = 'compress.zlib';
}
