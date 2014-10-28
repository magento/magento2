<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper that can convert relative paths from system.xml to absolute
 */
namespace Magento\Backend\Model\Config\Structure\Mapper\Helper;

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

        $realPath = array();
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
