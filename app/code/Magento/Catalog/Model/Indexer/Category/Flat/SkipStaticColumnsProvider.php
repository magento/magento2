<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Category\Flat;

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
     */
    public function get()
    {
        return $this->skipStaticColumns;
    }
}
