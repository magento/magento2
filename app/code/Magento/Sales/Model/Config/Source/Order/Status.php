<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Order Statuses source model
 */
namespace Magento\Sales\Model\Config\Source\Order;

/**
 * Provides a list of order statuses
 * @api
 * @since 100.0.2
 */
class Status implements \Magento\Framework\Option\ArrayInterface
{
    public const UNDEFINED_OPTION_LABEL = '-- Please Select --';

    /**
     * @var string[]
     */
    protected $_stateStatuses = [
        \Magento\Sales\Model\Order::STATE_NEW,
        \Magento\Sales\Model\Order::STATE_PROCESSING,
        \Magento\Sales\Model\Order::STATE_COMPLETE,
        \Magento\Sales\Model\Order::STATE_CLOSED,
        \Magento\Sales\Model\Order::STATE_CANCELED,
        \Magento\Sales\Model\Order::STATE_HOLDED,
    ];

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     */
    public function __construct(\Magento\Sales\Model\Order\Config $orderConfig)
    {
        $this->_orderConfig = $orderConfig;
    }

    /**
     * Returns a list of order statuses
     *
     * @return array
     */
    public function toOptionArray()
    {
        $statuses = $this->_stateStatuses
            ? $this->_orderConfig->getStateStatuses($this->_stateStatuses)
            : $this->_orderConfig->getStatuses();

        $undefinedOptionLabel = self::UNDEFINED_OPTION_LABEL;
        $options = [['value' => '', 'label' => __($undefinedOptionLabel)]];
        foreach ($statuses as $code => $label) {
            $options[] = ['value' => $code, 'label' => $label];
        }
        return $options;
    }
}
