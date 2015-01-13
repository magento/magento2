<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Resource;

use Magento\Framework\Data\AbstractSearchResult;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\SearchResultProcessorFactory;
use Magento\Framework\Data\SearchResultProcessor;
use Magento\Framework\DB\QueryInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Data\SearchResultIteratorFactory;

/**
 * CMS block model
 */
class AbstractCollection extends AbstractSearchResult
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SearchResultProcessor
     */
    protected $searchResultProcessor;

    /**
     * Table which links CMS entity to stores
     *
     * @var string
     */
    protected $storeTableName;

    /**
     * @var string
     */
    protected $linkFieldName;

    /**
     * @param QueryInterface $query
     * @param EntityFactoryInterface $entityFactory
     * @param ManagerInterface $eventManager
     * @param SearchResultIteratorFactory $resultIteratorFactory
     * @param StoreManagerInterface $storeManager
     * @param SearchResultProcessorFactory $searchResultProcessorFactory
     */
    public function __construct(
        QueryInterface $query,
        EntityFactoryInterface $entityFactory,
        ManagerInterface $eventManager,
        SearchResultIteratorFactory $resultIteratorFactory,
        StoreManagerInterface $storeManager,
        SearchResultProcessorFactory $searchResultProcessorFactory
    ) {
        $this->storeManager = $storeManager;
        $this->searchResultProcessor = $searchResultProcessorFactory->create($this);
        parent::__construct($query, $entityFactory, $eventManager, $resultIteratorFactory);
    }

    /**
     * @return void
     */
    protected function init()
    {
        $this->query->addCountSqlSkipPart(\Zend_Db_Select::GROUP, true);
    }

    /**
     * @return array
     */
    public function toOptionIdArray()
    {
        $res = [];
        $existingIdentifiers = [];
        foreach ($this->getItems() as $item) {
            /** @var \Magento\Cms\Model\Block|\Magento\Cms\Model\Page $item */
            $identifier = $item->getIdentifier();

            $data['value'] = $identifier;
            $data['label'] = $item->getTitle();

            if (in_array($identifier, $existingIdentifiers)) {
                $data['value'] .= '|' . $item->getId();
            } else {
                $existingIdentifiers[] = $identifier;
            }
            $res[] = $data;
        }
        return $res;
    }

    /**
     * Perform operations after collection load
     *
     * @return void
     */
    protected function afterLoad()
    {
        if ($this->getSearchCriteria()->getPart('first_store_flag')) {
            $items = $this->searchResultProcessor->getColumnValues($this->linkFieldName);

            $connection = $this->getQuery()->getConnection();
            $resource = $this->getQuery()->getResource();
            if (count($items)) {
                $select = $connection->select()->from(['cps' => $resource->getTable($this->storeTableName)])
                    ->where("cps.{$this->linkFieldName} IN (?)", $items);
                $result = $connection->fetchPairs($select);
                if ($result) {
                    foreach ($this->getItems() as $item) {
                        /** @var BlockInterface $item */
                        if (!isset($result[$item->getId()])) {
                            continue;
                        }
                        if ($result[$item->getId()] == 0) {
                            $stores = $this->storeManager->getStores(false, true);
                            $storeId = current($stores)->getId();
                            $storeCode = key($stores);
                        } else {
                            $storeId = $result[$item->getId()];
                            $storeCode = $this->storeManager->getStore($storeId)->getCode();
                        }
                        $item->setData('_first_store_id', $storeId);
                        $item->setData('store_code', $storeCode);
                        $item->setData('store_id', [$result[$item->getId()]]);
                    }
                }
            }
        }
        parent::afterLoad();
    }
}
