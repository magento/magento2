<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Model\Export;

use Magento\Framework\Data\Collection;
use Magento\ImportExport\Model\Export;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\Inventory\Model\ResourceModel\SourceItem\Collection as SourceItemCollection;
use Magento\Inventory\Model\ResourceModel\SourceItem\CollectionFactory;

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
     * @var CollectionFactory
     */
    private $sourceItemCollectionFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Export\Factory $collectionFactory
     * @param \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory
     * @param CollectionBuilder $collectionBuilder
     * @param CollectionFactory $sourceItemCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        CollectionBuilder $collectionBuilder,
        CollectionFactory $sourceItemCollectionFactory,
        array $data = []
    ) {
        $this->collectionBuilder = $collectionBuilder;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
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
     * Export process
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
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
     * @param Collection $collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function applyFilters(Collection $collection)
    {
        foreach ($this->retrieveFilterDataFromRequest() as $columnName => $value) {
            $attributeDefinition = $this->getAttributeCollection()->getItemById($columnName);
            $type = null;
            if ($attributeDefinition) {
                $type = $attributeDefinition->getData('backend_type');
            }

            $filterMethodName = 'applyFilterFor' . ucfirst($type);
            if (method_exists($this, $filterMethodName)) {
                $this->$filterMethodName($collection, $columnName, $value);
            } else {
                $this->applyDefaultFilter($collection, $columnName, $value);
            }
        }
    }

    /**
     * @param Collection $collection
     * @param string $columnName
     * @param mixed $value
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyFilterForInt(Collection $collection, $columnName, $value)
    {
        if (is_array($value)) {
            $from = $value[0] ?? null;
            $to = $value[1] ?? null;

            if (is_numeric($from) && !empty($from)) {
                $collection->addFieldToFilter($columnName, ['from' => $from]);
            }

            if (is_numeric($to) && !empty($to)) {
                $collection->addFieldToFilter($columnName, ['to' => $to]);
            }

            return;
        }

        $collection->addFieldToFilter($columnName, ['eq' => $value]);
    }

    /**
     * @param Collection $collection
     * @param string $columnName
     * @param mixed $value
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyFilterForDecimal(Collection $collection, $columnName, $value)
    {
        $this->applyFilterForInt($collection, $columnName, $value);
    }

    /**
     * @param Collection $collection
     * @param string $columnName
     * @param mixed $value
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyDefaultFilter(Collection $collection, $columnName, $value)
    {
        $collection->addFieldToFilter($columnName, ['like' => '%' . $value . '%']);
    }

    /**
     * @param Collection $collection
     * @param string $columnName
     * @param mixed $value
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyFilterForDatetime(Collection $collection, $columnName, $value)
    {
        if (is_array($value)) {
            $from = $value[0] ?? null;
            $to = $value[1] ?? null;

            if (is_scalar($from) && !empty($from)) {
                $date = (new \DateTime($from))->format('m/d/Y');
                $collection->addFieldToFilter($columnName, ['from' => $date, 'date' => true]);
            }

            if (is_scalar($to) && !empty($to)) {
                $date = (new \DateTime($to))->format('m/d/Y');
                $collection->addFieldToFilter($columnName, ['to' => $date, 'date' => true]);
            }

            return;
        }

        $this->applyDefaultFilter($collection, $columnName, $value);
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
     * Get header columns
     *
     * @return array
     * @throws \Exception
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
     * Export one item
     *
     * @param \Magento\Framework\Model\AbstractModel $item
     * @return void
     */
    public function exportItem($item)
    {
        // will not implement this method as it is legacy interface
    }

    /**
     * Entity type code getter
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'stock_sources';
    }

    /**
     * Get entity collection
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    protected function _getEntityCollection()
    {
        // will not implement this method as it is legacy interface
    }
}
