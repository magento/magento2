<?php
/**
 * Collection of various useful functions
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework;

class Util
{
    /**
     * Return PHP version without optional suffix
     * Scheme: major.minor.release
     * @return string
     */
    public function getTrimmedPhpVersion()
    {
        return implode('.', [PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION]);
    }
}
