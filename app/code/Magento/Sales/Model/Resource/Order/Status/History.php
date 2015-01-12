<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order\Status;

use Magento\Sales\Model\Order\Status\History\Validator;
use Magento\Sales\Model\Resource\Entity;
use Magento\Sales\Model\Spi\OrderStatusHistoryResourceInterface;

/**
 * Flat sales order status history resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class History extends Entity implements OrderStatusHistoryResourceInterface
{
    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Sales\Model\Resource\Attribute $attribute
     * @param \Magento\Sales\Model\Increment $salesIncrement
     * @param Validator $validator
     * @param \Magento\Sales\Model\Resource\GridInterface $gridAggregator
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Sales\Model\Resource\Attribute $attribute,
        \Magento\Sales\Model\Increment $salesIncrement,
        Validator $validator,
        \Magento\Sales\Model\Resource\GridInterface $gridAggregator = null
    ) {
        $this->validator = $validator;
        parent::__construct($resource, $attribute, $salesIncrement, $gridAggregator);
    }

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_status_history_resource';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_order_status_history', 'entity_id');
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_beforeSave($object);
        $warnings = $this->validator->validate($object);
        if (!empty($warnings)) {
            throw new \Magento\Framework\Model\Exception(
                __('Cannot save comment') . ":\n" . implode("\n", $warnings)
            );
        }
        return $this;
    }
}
