<?php
/**
 * Magento filesystem zlib driver
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Filesystem\Driver;

class Zlib extends File
{
    /**
     * @var string
     */
    protected $scheme = 'compress.zlib';
}
