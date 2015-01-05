<?php
/**
 * Order creditmemo configuration model
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Model\Order\Creditmemo;

class Config extends \Magento\Sales\Model\Order\Total\Config\Base
{
    /**
     * Cache key for collectors
     *
     * @var string
     */
    protected $_collectorsCacheKey = 'sorted_order_creditmemo_collectors';

    /**
     * @var string
     */
    protected $_configSection = 'order_creditmemo';
}
