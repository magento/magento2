<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Model\Export;

use Magento\Eav\Model\Entity\AttributeFactory;
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
     */
    public function export()
    {
        $writer = $this->getWriter();

        $columns  = $this->_getHeaderColumns();
        $writer->setHeaderCols($columns);

        /** @var SourceItemCollection $collection */
        $collection = $this->sourceItemCollectionFactory->create();
        $collection->addFieldToSelect($columns);

        foreach ($collection->getData() as $data) {
            unset($data[SourceItem::ID_FIELD_NAME]);
            $writer->writeRow($data);
        }

        return $writer->getContents();
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
        // will not implement it is legacy interface method
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
        // will not implement it is legacy interface method
    }
}
