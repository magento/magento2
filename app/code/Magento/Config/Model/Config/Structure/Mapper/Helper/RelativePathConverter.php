<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Helper that can convert relative paths from system.xml to absolute
 */
namespace Magento\Config\Model\Config\Structure\Mapper\Helper;

class RelativePathConverter
{
    /**
     * Convert relative path from system.xml to absolute
     *
     * @param string $nodePath
     * @param string $relativePath
     * @return string
     * @throws \InvalidArgumentException
     */
    public function convert($nodePath, $relativePath)
    {
        $nodePath = trim($nodePath);
        $relativePath = trim($relativePath);

        if (empty($nodePath) || empty($relativePath)) {
            throw new \InvalidArgumentException('Invalid arguments');
        }

        $relativePathParts = explode('/', $relativePath);
        $pathParts = explode('/', $nodePath);

        $relativePartsCount = count($relativePathParts);
        $pathPartsCount = count($pathParts);

        if ($relativePartsCount === 1 && $pathPartsCount > 1) {
            $relativePathParts = array_pad($relativePathParts, -$pathPartsCount, '*');
        }

        $realPath = [];
        foreach ($relativePathParts as $index => $path) {
            if ($path === '*') {
                if (false == array_key_exists($index, $pathParts)) {
                    throw new \InvalidArgumentException(
                        sprintf('Invalid relative path %s in %s node', $relativePath, $nodePath)
                    );
                }
                $path = $pathParts[$index];
            }
            $realPath[$index] = $path;
        }

        return implode('/', $realPath);
    }
}
