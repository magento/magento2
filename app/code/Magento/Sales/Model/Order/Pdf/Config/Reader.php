<?php
/**
 * Loads catalog attributes configuration from multiple XML files by merging them together
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Pdf\Config;

/**
 * Class \Magento\Sales\Model\Order\Pdf\Config\Reader
 *
 */
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
