<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Convert;

/**
 * Magento data size converter class
 * @since 2.1.0
 */
class DataSize
{
    /**
     * Converts a size value to bytes
     * Example input: 100 (bytes), 10K (kilobytes), 13M (megabytes), 2G (gigabytes)
     *
     * @param string $size
     * @return integer
     * @since 2.1.0
     */
    public function convertSizeToBytes($size)
    {
        if (!is_numeric($size)) {
            $type = strtoupper(substr($size, -1));
            $size = (int)$size;

            switch ($type) {
                case 'K':
                    $size *= 1024;
                    break;

                case 'M':
                    $size *= 1024 * 1024;
                    break;

                case 'G':
                    $size *= 1024 * 1024 * 1024;
                    break;

                default:
                    break;
            }
        }
        return (int)$size;
    }
}
