<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Model\Export;

use Magento\Framework\App\ObjectManager;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\Inventory\Model\ResourceModel\SourceItemFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;


/**
 * @inheritdoc
 */
class Sources extends AbstractEntity
{

    /**#@+
     * Attribute collection name
     */
    const ATTRIBUTE_COLLECTION_NAME = \Magento\Inventory\Model\ResourceModel\SourceItem\Collection::class;

    /**
     * @var SourceItemFactory
     */
    private $sourceItemFactory;


    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        SourceItemFactory $sourceItemFactory,
        array $data = []
    ) {

        $this->sourceItemFactory = $sourceItemFactory;
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);
    }

    /**
     * @return \Magento\Framework\Data\Collection|void
     */
    public function getAttributeCollection()
    {
        $objectManager = ObjectManager::getInstance();
        /**  @var \Magento\Framework\DataObject $dataObject * */
        $dataObject = $objectManager->create('\Magento\Framework\DataObject');
        $this->_attributeCollection->addItem($dataObject);
        return $this->_attributeCollection;
    }

    /**
     * Export process
     *
     * @return string
     */
    public function export()
    {
        return 'teststtststststtsts';
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
