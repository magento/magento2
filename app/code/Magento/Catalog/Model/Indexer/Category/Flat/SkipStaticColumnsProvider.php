<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model\Indexer\Category\Flat;

/**
 * Static columns provider
 *
 * @api
 * @since 101.0.0
 */
class SkipStaticColumnsProvider
{
    /**
     * @var array
     */
    private $skipStaticColumns;

    /**
     * SkipStaticColumnsProvider constructor.
     * @param array $skipStaticColumns
     */
    public function __construct($skipStaticColumns = [])
    {
        $this->skipStaticColumns = $skipStaticColumns;
    }

    /**
     * @return array
     * @since 101.0.0
     */
    public function get()
    {
        return $this->skipStaticColumns;
    }
}
