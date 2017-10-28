<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Model\Export;

use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Export;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\Inventory\Model\ResourceModel\SourceItem\Collection as SourceItemCollection;
use Magento\Inventory\Model\ResourceModel\SourceItem\CollectionFactory as SourceItemCollectionFactory;
use Exception;

/**
 * @inheritdoc
 */
class Sources extends AbstractEntity
{
    /**
     * @var CollectionBuilder
     */
    private $collectionBuilder;

    /**
     * @var SourceItemCollectionFactory
     */
    private $sourceItemCollectionFactory;

    /**
     * @var FilterProcessorAggregator
     */
    private $filterProcessor;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Export\Factory $collectionFactory
     * @param \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory
     * @param CollectionBuilder $collectionBuilder
     * @param SourceItemCollectionFactory $sourceItemCollectionFactory
     * @param FilterProcessorAggregator $filterProcessor
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        CollectionBuilder $collectionBuilder,
        SourceItemCollectionFactory $sourceItemCollectionFactory,
        FilterProcessorAggregator $filterProcessor,
        array $data = []
    ) {
        $this->collectionBuilder = $collectionBuilder;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
        $this->filterProcessor = $filterProcessor;
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function getAttributeCollection()
    {
        return $this->collectionBuilder->create();
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function export()
    {
        $writer = $this->getWriter();

        $columns  = $this->_getHeaderColumns();
        $writer->setHeaderCols($columns);

        /** @var SourceItemCollection $collection */
        $collection = $this->sourceItemCollectionFactory->create();
        $collection->addFieldToSelect($columns);

        $this->applyFilters($collection);

        foreach ($collection->getData() as $data) {
            unset($data[SourceItem::ID_FIELD_NAME]);
            $writer->writeRow($data);
        }

        return $writer->getContents();
    }

    /**
     * @param SourceItemCollection $collection
     * @throws LocalizedException
     */
    private function applyFilters(SourceItemCollection $collection)
    {
        foreach ($this->retrieveFilterDataFromRequest() as $columnName => $value) {
            $attributeDefinition = $this->getAttributeCollection()->getItemById($columnName);
            if (!$attributeDefinition) {
                throw new LocalizedException(__(
                    'Given column name "%columnName" is not present in collection.',
                    ['columnName' => $columnName]
                ));
            }

            $type = $attributeDefinition->getData('backend_type');
            if (!$type) {
                throw new LocalizedException(__(
                    'There is no backend type specified for column "%columnName".',
                    ['columnName' => $columnName]
                ));
            }

            $this->filterProcessor->process($type, $collection, $columnName, $value);
        }
    }

    /**
     * @return array
     */
    private function retrieveFilterDataFromRequest()
    {
        return array_filter(
            $this->_parameters[Export::FILTER_ELEMENT_GROUP] ?? [],
            function($value) {
                return $value !== '';
            }
        );
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    protected function _getHeaderColumns()
    {
        $columns = [];
        foreach ($this->getAttributeCollection()->getItems() as $item) {
            $columns[] = $item->getData('id');
        }

        if (!isset($this->_parameters[Export::FILTER_ELEMENT_SKIP])) {
            return $columns;
        }

        // remove the skipped from columns
        $skippedAttributes = array_flip($this->_parameters[Export::FILTER_ELEMENT_SKIP]);
        foreach ($columns as $key => $value) {
            if (array_key_exists($value, $skippedAttributes) === true) {
                unset($columns[$key]);
            }
        }

        return $columns;
    }

    /**
     * @inheritdoc
     */
    public function exportItem($item)
    {
        // will not implement this method as it is legacy interface
    }

    /**
     * @inheritdoc
     */
    public function getEntityTypeCode()
    {
        return 'stock_sources';
    }

    /**
     * @inheritdoc
     */
    protected function _getEntityCollection()
    {
        // will not implement this method as it is legacy interface
    }
}
