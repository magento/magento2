<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Framework\Data;

/**
 * Dependency
 * @since 2.0.0
 */
class Dependency
{
    /**
     * Lib we depend on
     *
     * @var string
     * @since 2.0.0
     */
    protected $lib;

    /**
     * Dependencies count
     *
     * @var int
     * @since 2.0.0
     */
    protected $count;

    /**
     * Dependency construct
     *
     * @param string $lib
     * @param int $count
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getLib()
    {
        return $this->lib;
    }

    /**
     * Get count
     *
     * @return int
     * @since 2.0.0
     */
    public function getCount()
    {
        return $this->count;
    }
}
