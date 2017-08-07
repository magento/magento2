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
 * @since 2.1.0
 */
class SkipStaticColumnsProvider
{
    /**
     * @var array
     * @since 2.1.0
     */
    private $skipStaticColumns;

    /**
     * SkipStaticColumnsProvider constructor.
     * @param array $skipStaticColumns
     * @since 2.1.0
     */
    public function __construct($skipStaticColumns = [])
    {
        $this->skipStaticColumns = $skipStaticColumns;
    }

    /**
     * @return array
     * @since 2.1.0
     */
    public function get()
    {
        return $this->skipStaticColumns;
    }
}
