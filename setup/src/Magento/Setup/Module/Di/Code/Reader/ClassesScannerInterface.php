<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Reader;

/**
 * Interface ClassesScannerInterface
 *
 * @package Magento\Setup\Module\Di\Code\Reader
 * @since 2.0.0
 */
interface ClassesScannerInterface
{
    /**
     * Retrieves list of classes for given path
     *
     * @param string $path path to dir with files
     *
     * @return array
     * @since 2.0.0
     */
    public function getList($path);
}
