<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Model\Export;

use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\Inventory\Model\ResourceModel\SourceItemFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * @inheritdoc
 */
class Sources extends AbstractEntity
{

    /**
     * @var SourceItemFactory
     */
    private $sourceItemFactory;

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        SourceItemFactory $sourceItemFactory,
        AttributeFactory $attributeFactory,
        array $data = []
    ) {
        $this->sourceItemFactory = $sourceItemFactory;
        $this->attributeFactory =  $attributeFactory;
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);
    }

    /**
     * @inheritdoc
     */
    public function getAttributeCollection()
    {
        if (count($this->_attributeCollection) === 0) {
            /** @var   \Magento\Eav\Model\Entity\Attribute $skuAttribute */
            $skuAttribute = $this->attributeFactory->create();
            $skuAttribute->setDefaultFrontendLabel(SourceItemInterface::SKU);
            $skuAttribute->setAttributeCode(SourceItemInterface::SKU);
            $this->_attributeCollection->addItem($skuAttribute);

            /** @var   \Magento\Eav\Model\Entity\Attribute   $sourceIdAttribute */
            $sourceIdAttribute = $this->attributeFactory->create();
            $sourceIdAttribute->setDefaultFrontendLabel(SourceItemInterface::SOURCE_ID);
            $sourceIdAttribute->setAttributeCode(SourceItemInterface::SOURCE_ID);
            $this->_attributeCollection->addItem($sourceIdAttribute);

            /** @var   \Magento\Eav\Model\Entity\Attribute $skuAttribute */
            $quantityAttribute = $this->attributeFactory->create();
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

    }

    /**
     * Export one item
     *
     * @param \Magento\Framework\Model\AbstractModel $item
     * @return void
     */
    public function exportItem($item)
    {
        // TODO: Implement exportItem() method.
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
     * Get header columns
     *
     * @return array
     */
    protected function _getHeaderColumns()
    {
        return [
            SourceItemInterface::SOURCE_ID,
            SourceItemInterface::SKU,
            SourceItemInterface::STATUS,
            SourceItemInterface::QUANTITY
        ];
    }

    /**
     * Get entity collection
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    protected function _getEntityCollection()
    {
        return $this->sourceItemFactory->create();
    }
}
