<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Scanner;

class DirectoryScanner
{
    /**
     * Scan directory
     *
     * @param string $dir
     * @param array $patterns
     * @param string[] $excludePatterns
     * @return array
     */
    public function scan($dir, array $patterns = [], array $excludePatterns = [])
    {
        $recursiveIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::FOLLOW_SYMLINKS)
        );
        $output = [];
        foreach ($recursiveIterator as $file) {
            /** @var $file \SplFileInfo */
            if ($file->isDir()) {
                continue;
            }

            $filePath = str_replace('\\', '/', $file->getRealPath());
            if (!empty($excludePatterns)) {
                foreach ($excludePatterns as $excludePattern) {
                    if (preg_match($excludePattern, $filePath)) {
                        continue 2;
                    }
                }
            }
            foreach ($patterns as $type => $pattern) {
                if (preg_match($pattern, $filePath)) {
                    $output[$type][] = $filePath;
                    break;
                }
            }
        }
        return $output;
    }
}
