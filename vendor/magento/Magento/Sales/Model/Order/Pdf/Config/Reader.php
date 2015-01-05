<?php
/**
 * Loads catalog attributes configuration from multiple XML files by merging them together
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
