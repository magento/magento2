<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * @since 101.0.0
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
