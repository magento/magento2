<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
