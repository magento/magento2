<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Resource\Page;

use Magento\Framework\Data\AbstractSearchResult;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Data\SearchResultIteratorFactory;
use Magento\Framework\DB\QueryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\SearchResultProcessorFactory;
use Magento\Framework\Data\SearchResultProcessor;

/**
 * CMS page collection
 */
class Collection extends AbstractSearchResult
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
        $this->setDataInterfaceName('Magento\Cms\Model\Page');
        $this->query->addCountSqlSkipPart(\Zend_Db_Select::GROUP, true);
        $this->storeTableName = 'cms_page_store';
        $this->linkFieldName = 'page_id';
    }

    /**
     * @return array
     */
    public function toOptionIdArray()
    {
        $res = [];
        $existingIdentifiers = [];
        foreach ($this->getItems() as $item) {
            /** @var PageInterface $item */
            $identifier = $item->getIdentifier();

            $data['value'] = $identifier;
            $data['label'] = $item->getTitle();

            if (in_array($identifier, $existingIdentifiers)) {
                $data['value'] .= '|' . $item->getPageId();
            } else {
                $existingIdentifiers[] = $identifier;
            }
            $res[] = $data;
        }
        return $res;
    }

    /**
     * @deprecated
     * @return void
     */
    public function addStoreFilter()
    {
        //
    }

    /**
     * Perform operations after collection load
     *
     * @return void
     */
    protected function afterLoad()
    {
        if ($this->getSearchCriteria()->getPart('first_store_flag')) {
            $items = $this->searchResultProcessor->getColumnValues('page_id');

            $connection = $this->getQuery()->getConnection();
            $resource = $this->getQuery()->getResource();
            if (count($items)) {
                $select = $connection->select()->from(['cps' => $resource->getTable('cms_page_store')])
                    ->where('cps.page_id IN (?)', $items);
                if ($result = $connection->fetchPairs($select)) {
                    foreach ($this->getItems() as $item) {
                        /** @var PageInterface $item */
                        if (!isset($result[$item->getPageId()])) {
                            continue;
                        }
                        if ($result[$item->getPageId()] == 0) {
                            $stores = $this->storeManager->getStores(false, true);
                            $storeId = current($stores)->getId();
                            $storeCode = key($stores);
                        } else {
                            $storeId = $result[$item->getPageId()];
                            $storeCode = $this->storeManager->getStore($storeId)->getCode();
                        }
                        $item->setData('_first_store_id', $storeId);
                        $item->setData('store_code', $storeCode);
                        $item->setData('store_id', [$result[$item->getPageId()]]);
                    }
                }
            }
        }
        parent::afterLoad();
    }
}
