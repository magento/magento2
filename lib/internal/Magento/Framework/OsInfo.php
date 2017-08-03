<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * Wrapper on PHP_OS constant
 * @since 2.0.0
 */
class OsInfo
{
    /**
     * Operation system
     *
     * @var string
     * @since 2.0.0
     */
    protected $os;

    /**
     * Initialize os
     * @since 2.0.0
     */
    public function __construct()
    {
        $this->os = PHP_OS;
    }

    /**
     * Check id system is Windows
     *
     * @return bool
     * @since 2.0.0
     */
    public function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}
