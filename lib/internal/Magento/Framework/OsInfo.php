<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * Wrapper on PHP_OS constant
 */
class OsInfo
{
    /**
     * Operation system
     *
     * @var string
     */
    protected $os;

    /**
     * Initialize os
     */
    public function __construct()
    {
        $this->os = PHP_OS;
    }

    /**
     * Check id system is Windows
     *
     * @return bool
     */
    public function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}
