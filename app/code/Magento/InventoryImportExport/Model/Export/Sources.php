<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Model\Export;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\ImportExport\Model\Export\Factory as ExportFactory;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\Inventory\Model\ResourceModel\SourceItem\Collection as SourceItemCollection;
use Magento\InventoryImportExport\Model\Export\SourceItemCollectionFactoryInterface;
use Magento\InventoryImportExport\Model\Export\ColumnProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;

/**
 * @inheritdoc
 */
class Sources extends AbstractEntity
{
    /**
     * @var AttributeCollectionProvider
     */
    private $attributeCollectionProvider;

    /**
     * @var SourceItemCollectionFactoryInterface
     */
    private $sourceItemCollectionFactory;

    /**
     * @var ColumnProviderInterface
     */
    private $columnProvider;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ExportFactory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param AttributeCollectionProvider $attributeCollectionProvider
     * @param SourceItemCollectionFactoryInterface $sourceItemCollectionFactory
     * @param ColumnProviderInterface $columnProvider
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ExportFactory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        AttributeCollectionProvider $attributeCollectionProvider,
        SourceItemCollectionFactoryInterface $sourceItemCollectionFactory,
        ColumnProviderInterface $columnProvider,
        array $data = []
    ) {
        $this->attributeCollectionProvider = $attributeCollectionProvider;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
        $this->columnProvider = $columnProvider;
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function getAttributeCollection()
    {
        return $this->attributeCollectionProvider->get();
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function export()
    {
        $writer = $this->getWriter();
        $writer->setHeaderCols($this->_getHeaderColumns());

        /** @var SourceItemCollection $collection */
        $collection = $this->sourceItemCollectionFactory->create(
            $this->getAttributeCollection(),
            $this->_parameters
        );

        foreach ($collection->getData() as $data) {
            unset($data[SourceItem::ID_FIELD_NAME]);
            $writer->writeRow($data);
        }

        return $writer->getContents();
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    protected function _getHeaderColumns()
    {
        return $this->columnProvider->getHeaders($this->getAttributeCollection(), $this->_parameters);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
