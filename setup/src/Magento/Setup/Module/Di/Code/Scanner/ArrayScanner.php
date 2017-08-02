<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Scanner;

/**
 * Class \Magento\Setup\Module\Di\Code\Scanner\ArrayScanner
 *
 * @since 2.0.0
 */
class ArrayScanner implements ScannerInterface
{
    /**
     * Scan files
     *
     * @param array $files
     * @return array
     * @since 2.0.0
     */
    public function collectEntities(array $files)
    {
        $output = [];
        foreach ($files as $file) {
            if (file_exists($file)) {
                $data = include $file;
                $output = array_merge($output, $data);
            }
        }
        return $output;
    }
}
