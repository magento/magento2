<?php
/**
 * Magento filesystem zlib driver
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Driver;

class Zlib extends File
{
    /**
     * @var string
     */
    protected $scheme = 'compress.zlib';
}
