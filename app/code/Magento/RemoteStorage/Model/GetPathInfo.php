<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Model;

/**
 * Retrieve path info service.
 */
class GetPathInfo
{
    /**
     * Retrieve path info from given path.
     *
     * @param string $path
     * @return string[]
     */
    public function execute(string $path): array
    {
        $pathInfo = compact('path');
        if ('' !== $dirname = dirname($path)) {
            $pathInfo['dirname'] = $dirname === '.' ? '' : $dirname;
        }
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        $pathInfo['basename'] = preg_replace('#.*?([^' . preg_quote(DIRECTORY_SEPARATOR, '#') . ']+$)#', '$1', $path);
        $pathInfo += pathinfo($pathInfo['basename']);

        return $pathInfo + ['dirname' => ''];
    }
}
