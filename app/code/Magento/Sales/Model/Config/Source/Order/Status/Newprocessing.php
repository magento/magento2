<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Config\Source\Order\Status;

/**
 * Order Statuses source model
 * @since 2.0.0
 */
class Newprocessing extends \Magento\Sales\Model\Config\Source\Order\Status
{
    /**
     * @var string[]
     * @since 2.0.0
     */
    protected $_stateStatuses = [
        \Magento\Sales\Model\Order::STATE_NEW,
        \Magento\Sales\Model\Order::STATE_PROCESSING,
    ];
}
