<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product\Eav;

/**
 * Abstract action reindex class
 */
abstract class AbstractAction
{
    /**
     * Config path for enable EAV indexer
     */
    const ENABLE_EAV_INDEXER = 'catalog/search/enable_eav_indexer';

    /**
     * EAV Indexers by type
     *
     * @var array
     */
    protected $_types;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory
     */
    protected $_eavSourceFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory
     */
    protected $_eavDecimalFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * AbstractAction constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory $eavDecimalFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory $eavSourceFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface|null $scopeConfig
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory $eavDecimalFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory $eavSourceFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig = null
    ) {
        $this->_eavDecimalFactory = $eavDecimalFactory;
        $this->_eavSourceFactory = $eavSourceFactory;
        $this->scopeConfig = $scopeConfig ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        );
    }

    /**
     * Execute action for given ids
     *
     * @param array|int $ids
     * @return void
     */
    abstract public function execute($ids);

    /**
     * Retrieve array of EAV type indexers
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\AbstractEav[]
     */
    public function getIndexers()
    {
        if ($this->_types === null) {
            $this->_types = [
                'source' => $this->_eavSourceFactory->create(),
                'decimal' => $this->_eavDecimalFactory->create(),
            ];
        }

        return $this->_types;
    }

    /**
     * Retrieve indexer instance by type
     *
     * @param string $type
     * @return \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\AbstractEav
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getIndexer($type)
    {
        $indexers = $this->getIndexers();
        if (!isset($indexers[$type])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unknown EAV indexer type "%1".', $type));
        }
        return $indexers[$type];
    }

    /**
     * Reindex entities
     *
     * @param null|array|int $ids
     * @throws \Exception
     * @return void
     */
    public function reindex($ids = null)
    {
        if (!$this->isEavIndexerEnabled()) {
            return;
        }
        foreach ($this->getIndexers() as $indexer) {
            if ($ids === null) {
                $indexer->reindexAll();
            } else {
                if (!is_array($ids)) {
                    $ids = [$ids];
                }
                $ids = $this->processRelations($indexer, $ids);
                $indexer->reindexEntities($ids);
                $destinationTable = $indexer->getMainTable();
                $this->syncData($indexer, $destinationTable, $ids);
            }
        }
    }

    /**
     * Synchronize data between index storage and original storage
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\AbstractEav $indexer
     * @param string $destinationTable
     * @param array $ids
     * @throws \Exception
     * @return void
     */
    protected function syncData($indexer, $destinationTable, $ids)
    {
        $connection = $indexer->getConnection();
        $connection->beginTransaction();
        try {
            // remove old index
            $where = $connection->quoteInto('entity_id IN(?)', $ids);
            $connection->delete($destinationTable, $where);
            // insert new index
            $indexer->insertFromTable($indexer->getIdxTable(), $destinationTable);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * Retrieve product relations by children and parent
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\AbstractEav $indexer
     * @param array $ids
     *
     * @param bool $onlyParents
     * @return array $ids
     */
    protected function processRelations($indexer, $ids, $onlyParents = false)
    {
        $parentIds = $indexer->getRelationsByChild($ids);
        $parentIds = array_unique(array_merge($parentIds, $ids));
        $childIds = $onlyParents ? [] : $indexer->getRelationsByParent($parentIds);
        return array_unique(array_merge($ids, $childIds, $parentIds));
    }

    /**
     * Get EAV indexer status
     *
     * @return bool
     */
    private function isEavIndexerEnabled(): bool
    {
        $eavIndexerStatus = $this->scopeConfig->getValue(
            self::ENABLE_EAV_INDEXER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return (bool)$eavIndexerStatus;
    }
}
