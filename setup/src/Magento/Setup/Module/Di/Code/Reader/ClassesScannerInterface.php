<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
