<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Model\Export;

use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\Data\Collection;
use Magento\ImportExport\Model\Export;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\Inventory\Model\ResourceModel\SourceItem\Collection as SourceItemCollection;
use Magento\Inventory\Model\ResourceModel\SourceItem\CollectionFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * @inheritdoc
 */
class Sources extends AbstractEntity
{

    /**
     * @var CollectionFactory
     */
    private $sourceItemCollectionFactory;

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        CollectionFactory $sourceItemCollectionFactory,
        AttributeFactory $attributeFactory,
        array $data = []
    ) {
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
        $this->attributeFactory = $attributeFactory;
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);
    }

    /**
     * @inheritdoc
     */
    public function getAttributeCollection()
    {
        if (count($this->_attributeCollection) === 0) {
            /** @var \Magento\Eav\Model\Entity\Attribute $skuAttribute */
            $skuAttribute = $this->attributeFactory->create();
            $skuAttribute->setId(SourceItemInterface::SKU);
            $skuAttribute->setDefaultFrontendLabel(SourceItemInterface::SKU);
            $skuAttribute->setAttributeCode(SourceItemInterface::SKU);
            $this->_attributeCollection->addItem($skuAttribute);

            /** @var \Magento\Eav\Model\Entity\Attribute $sourceIdAttribute */
            $sourceIdAttribute = $this->attributeFactory->create();
            $sourceIdAttribute->setId(SourceItemInterface::SOURCE_ID);
            $sourceIdAttribute->setDefaultFrontendLabel(SourceItemInterface::SOURCE_ID);
            $sourceIdAttribute->setAttributeCode(SourceItemInterface::SOURCE_ID);
            $sourceIdAttribute->setBackendType('int');
            $this->_attributeCollection->addItem($sourceIdAttribute);

            /** @var \Magento\Eav\Model\Entity\Attribute $statusIdAttribut */
            $statusIdAttribute = $this->attributeFactory->create();
            $statusIdAttribute->setId(SourceItemInterface::STATUS);
            $statusIdAttribute->setDefaultFrontendLabel(SourceItemInterface::STATUS);
            $statusIdAttribute->setAttributeCode(SourceItemInterface::STATUS);
            $this->_attributeCollection->addItem($statusIdAttribute);

            /** @var \Magento\Eav\Model\Entity\Attribute $quantityAttribute */
            $quantityAttribute = $this->attributeFactory->create();
            $quantityAttribute->setId(SourceItemInterface::QUANTITY);
            $sourceIdAttribute->setBackendType('decimal');
            $quantityAttribute->setDefaultFrontendLabel(SourceItemInterface::QUANTITY);
            $quantityAttribute->setAttributeCode(SourceItemInterface::QUANTITY);
            $this->_attributeCollection->addItem($quantityAttribute);
        }

        return $this->_attributeCollection;
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

            if (is_array($value)) {
                $from = $value[0] ?? null;
                $to = $value[1] ?? null;

                if (in_array($type, ['int', 'decimal'], true)) {
                    if (is_numeric($from) && !empty($from)) {
                        $collection->addFieldToFilter($columnName, ['from' => $from]);
                    }
                    if (is_numeric($to) && !empty($to)) {
                        $collection->addFieldToFilter($columnName, ['to' => $to]);
                    }
                } elseif ($type === 'datetime') {
                    if (is_scalar($from) && !empty($from)) {
                        $date = (new \DateTime($from))->format('m/d/Y');
                        $collection->addFieldToFilter($columnName, ['from' => $date, 'date' => true]);
                    }
                    if (is_scalar($to) && !empty($to)) {
                        $date = (new \DateTime($to))->format('m/d/Y');
                        $collection->addFieldToFilter($columnName, ['to' => $date, 'date' => true]);
                    }
                }

                continue;
            }

            $collection->addFieldToFilter($columnName, ['like' => '%' . $value . '%']);
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
                return !empty($value);
            }
        );
    }

    /**
     * Get header columns
     *
     * @return array
     */
    protected function _getHeaderColumns()
    {
        $columns = [
            SourceItemInterface::SOURCE_ID,
            SourceItemInterface::SKU,
            SourceItemInterface::STATUS,
            SourceItemInterface::QUANTITY
        ];

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
