<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\ResourceModel\Design\Config;

use Magento\Config\Model\ResourceModel\Config\Data\Collection as ConfigCollection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Theme\Model\Design\Config\ValueProcessor;
use Psr\Log\LoggerInterface;

class Collection extends ConfigCollection
{
    /**
     * @var \Magento\Theme\Model\Design\Config\ValueProcessor
     */
    protected $valueProcessor;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param ValueProcessor $valueProcessor
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        ValueProcessor $valueProcessor,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->valueProcessor = $valueProcessor;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Add paths filter to collection
     *
     * @param array $paths
     * @return $this
     */
    public function addPathsFilter(array $paths)
    {
        $this->addFieldToFilter('path', ['in' => $paths]);
        return $this;
    }

    /**
     * Add scope ID filter to collection
     *
     * @param int $scopeId
     * @return $this
     */
    public function addScopeIdFilter($scopeId)
    {
        $this->addFieldToFilter('scope_id', (int)$scopeId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function _afterLoad()
    {
        foreach ($this->_items as $item) {
            $item->setData(
                'value',
                $this->valueProcessor->process(
                    $item->getData('value'),
                    $this->getData('scope'),
                    $this->getData('scope_id'),
                    $item->getData('path')
                )
            );
        }
        parent::_afterLoad();
    }
}
