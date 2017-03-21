<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Config\FileResolver;

use Magento\Mtf\Util\Iterator\File;

/**
 * Provides MTF configuration file from specified scope.
 */
class ScopeConfig extends Primary
{
    /**
     * Retrieve the configuration file with given name that relate to MTF global configuration.
     *
     * @param string $filename
     * @param string $scope
     * @return File|array
     */
    public function get($filename, $scope)
    {
        return new File([MTF_BP . DIRECTORY_SEPARATOR . $scope . DIRECTORY_SEPARATOR . $filename]);
    }
}
