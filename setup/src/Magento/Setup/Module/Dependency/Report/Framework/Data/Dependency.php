<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Framework\Data;

/**
 * Dependency
 */
class Dependency
{
    /**
     * Lib we depend on
     *
     * @var string
     */
    protected $lib;

    /**
     * Dependencies count
     *
     * @var int
     */
    protected $count;

    /**
     * Dependency construct
     *
     * @param string $lib
     * @param int $count
     */
    public function __construct($lib, $count)
    {
        $this->lib = $lib;
        $this->count = $count;
    }

    /**
     * Get lib
     *
     * @return string
     */
    public function getLib()
    {
        return $this->lib;
    }

    /**
     * Get count
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }
}
