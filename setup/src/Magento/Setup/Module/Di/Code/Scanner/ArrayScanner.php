<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Scanner;

class ArrayScanner implements ScannerInterface
{
    /**
     * Scan files
     *
     * @param array $files
     * @return array
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
