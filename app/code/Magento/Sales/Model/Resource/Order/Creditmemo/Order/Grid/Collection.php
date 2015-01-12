<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order\Creditmemo\Order\Grid;

/**
 * Flat sales order creditmemo collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Sales\Model\Resource\Order\Creditmemo\Grid\Collection
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registryManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Registry $registryManager
     * @param null $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $registryManager,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->registryManager = $registryManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->registryManager->registry('current_order');
    }

    /**
     * Apply sorting and filtering to collection
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFieldToSelect(
            'entity_id'
        )->addFieldToSelect(
            'created_at'
        )->addFieldToSelect(
            'increment_id'
        )->addFieldToSelect(
            'order_currency_code'
        )->addFieldToSelect(
            'store_currency_code'
        )->addFieldToSelect(
            'base_currency_code'
        )->addFieldToSelect(
            'state'
        )->addFieldToSelect(
            'grand_total'
        )->addFieldToSelect(
            'base_grand_total'
        )->addFieldToSelect(
            'billing_name'
        )->setOrderFilter(
            $this->getOrder()
        );
        return $this;
    }
}
