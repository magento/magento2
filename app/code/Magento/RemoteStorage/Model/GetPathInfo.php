<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Model;

/**
 * Retrieve path info service.
 *
 */
class GetPathInfo
{
    /**
     * Retrieve path info from given path
     *
     * @param string $path
     * @return string[]
     */
    public function execute(string $path): array
    {
        $pathinfo = compact('path');

        if ('' !== $dirname = dirname($path)) {
            $pathinfo['dirname'] = $dirname === '.' ? '' : $dirname;
        }

        $pathinfo['basename'] = $this->basename($path);

        $pathinfo += pathinfo($pathinfo['basename']);

        return $pathinfo + ['dirname' => ''];
    }

    /**
     * Returns the trailing name component of the path.
     *
     * @param string $path
     *
     * @return string
     */
    private function basename($path)
    {
        $separators = DIRECTORY_SEPARATOR === '/' ? '/' : '\/';

        $path = rtrim($path, $separators);

        $basename = preg_replace('#.*?([^' . preg_quote($separators, '#') . ']+$)#', '$1', $path);

        if (DIRECTORY_SEPARATOR === '/') {
            return $basename;
        }
        // @codeCoverageIgnoreStart
        // Extra Windows path munging. This is tested via AppVeyor, but code
        // coverage is not reported.

        // Handle relative paths with drive letters. c:file.txt.
        while (preg_match('#^[a-zA-Z]{1}:[^\\\/]#', $basename)) {
            $basename = substr($basename, 2);
        }

        // Remove colon for standalone drive letter names.
        if (preg_match('#^[a-zA-Z]{1}:$#', $basename)) {
            $basename = rtrim($basename, ':');
        }

        return $basename;
        // @codeCoverageIgnoreEnd
    }
}
