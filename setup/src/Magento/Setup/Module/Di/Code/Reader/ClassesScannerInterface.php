<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Reader;

/**
 * Interface ClassesScannerInterface
 *
 * @package Magento\Setup\Module\Di\Code\Reader
 */
interface ClassesScannerInterface
{

    /**
     * Retrieves list of classes for given path
     *
     * @param string $path path to dir with files
     *
     * @return array
     */
    public function getList($path);
}
