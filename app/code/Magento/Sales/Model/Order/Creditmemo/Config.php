<?php
/**
 * Order creditmemo configuration model
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
