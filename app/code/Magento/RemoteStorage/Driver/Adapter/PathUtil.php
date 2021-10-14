<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver\Adapter;

/**
 * Utility class for path operations.
 */
class PathUtil
{
    /**
     * Get normalized path info.
     *
     * @param string $path
     * @return array
     */
    public function pathInfo($path)
    {
        $pathInfo = ['path' => $path];
        $dirname = dirname($path);
        if ('' !== $dirname) {
            $pathInfo['dirname'] = $dirname === '.' ? '' : $dirname;
        }
        $pathInfo['basename'] = $this->basename($path);
        $pathInfo += pathinfo($pathInfo['basename']);
        return $pathInfo + ['dirname' => ''];
    }

    /**
     * Get basename for path.
     *
     * @param string $path
     * @return string
     */
    private function basename($path)
    {
        $separators = DIRECTORY_SEPARATOR === '/' ? '/' : '\/';
        $path = rtrim($path, $separators);
        return preg_replace('#.*?([^' . preg_quote($separators, '#') . ']+$)#', '$1', $path);
    }
}
