<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\Code\Scanner;

class DirectoryScanner
{
    /**
     * Scan directory
     *
     * @param string $dir
     * @param array $patterns
     * @return array
     */
    public function scan($dir, array $patterns = [])
    {
        $output = [];
        /** @var $file \DirectoryIterator */
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
            if ($file->isDir()) {
                continue;
            }

            foreach ($patterns as $type => $pattern) {
                $filePath = str_replace('\\', '/', $file->getRealPath());
                if (preg_match($pattern, $filePath)) {
                    $output[$type][] = $filePath;
                    break;
                }
            }
        }
        return $output;
    }
}
