<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel\Entity\Attribute\Grid;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Eav Resource Attribute Set Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registryManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Registry $registryManager
     * @param mixed $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $registryManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_registryManager = $registryManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     *  Add filter by entity type id to collection
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|$this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->setEntityTypeFilter($this->_registryManager->registry('entityType'));
        return $this;
    }
}
