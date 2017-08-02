<?php
/**
 * Scoped config data collection
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\ResourceModel\Config\Collection;

/**
 * Class \Magento\Store\Model\ResourceModel\Config\Collection\Scoped
 *
 * @since 2.0.0
 */
class Scoped extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Scope to filter by
     *
     * @var string
     * @since 2.0.0
     */
    protected $_scope;

    /**
     * Scope id to filter by
     *
     * @var int
     * @since 2.0.0
     */
    protected $_scopeId;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Config\Model\ResourceModel\Config\Data $resource
     * @param string $scope
     * @param mixed $connection
     * @param mixed $scopeId
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Config\Model\ResourceModel\Config\Data $resource,
        $scope,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        $scopeId = null
    ) {
        $this->_scope = $scope;
        $this->_scopeId = $scopeId;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Initialize select
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFieldToSelect(['path', 'value'])->addFieldToFilter('scope', $this->_scope);

        if ($this->_scopeId !== null) {
            $this->addFieldToFilter('scope_id', $this->_scopeId);
        }
        return $this;
    }
}
