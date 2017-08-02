<?php
/**
 * Order creditmemo configuration model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

/**
 * @api
 * @since 2.0.0
 */
class Config extends \Magento\Sales\Model\Order\Total\Config\Base
{
    /**
     * Cache key for collectors
     *
     * @var string
     * @since 2.0.0
     */
    protected $_collectorsCacheKey = 'sorted_order_creditmemo_collectors';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_configSection = 'order_creditmemo';
}
