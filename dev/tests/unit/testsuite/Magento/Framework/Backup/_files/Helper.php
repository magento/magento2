<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Mock Filesystem helper
 */
namespace Magento\Framework\Backup\Filesystem;

class Helper
{
    /**
     * Constant can be used in getInfo() function as second parameter.
     * Check whether directory and all files/sub directories are readable
     *
     * @const int
     */
    const INFO_READABLE = 2;

    /**
     * Constant can be used in getInfo() function as second parameter.
     * Get directory size
     *
     * @const int
     */
    const INFO_SIZE = 4;

    /**
     * Mock Get information (readable, writable, size) about $path
     *
     * @param $path
     * @param int $infoOptions
     * @param array $skipFiles
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getInfo($path, $infoOptions = self::INFO_ALL, $skipFiles = [])
    {
        return ['readable' => true, 'size' => 1];
    }
}
