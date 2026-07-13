<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\Order\Pdf\Config;

class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of identifier attributes for merging
     *
     * @var array
     */
    protected $_idAttributes = [
        '/config/renderers/page' => 'type',
        '/config/renderers/page/renderer' => 'product_type',
        '/config/totals/total' => 'name',
    ];
}
