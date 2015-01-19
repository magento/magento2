<?php
/**
 * Loads catalog attributes configuration from multiple XML files by merging them together
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
