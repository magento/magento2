<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;

/**
 * DuplicatedProductAttributesCopier
 *
 * Is used to copy product attributes related to non-global scope
 * from source to target product during product duplication
 */
class DuplicatedProductAttributesCopier
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param MetadataPool $metadataPool
     * @param CollectionFactory $collectionFactory
     * @param ResourceConnection $resource
     */
    public function __construct(
        MetadataPool $metadataPool,
        CollectionFactory $collectionFactory,
        ResourceConnection $resource
    ) {
        $this->metadataPool = $metadataPool;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
    }

    /**
     * Copy non-global Product Attributes form source to target
     *
     * @param $source Product
     * @param $target Product
     * @return void
     */
    public function copyProductAttributes(Product $source, Product $target): void
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();
        $attributeCollection = $this->collectionFactory->create()
            ->setAttributeSetFilter($source->getAttributeSetId())
            ->addFieldToFilter('backend_type', ['neq' => 'static'])
            ->addFieldToFilter('is_global', 0);

        $eavTableNames = [];
        foreach ($attributeCollection->getItems() as $item) {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $item */
            $eavTableNames[] = $item->getBackendTable();
        }

        $connection = $this->resource->getConnection();
        foreach (array_unique($eavTableNames) as $eavTable) {
            $select = $connection->select()
                ->from(
                    ['main_table' => $this->resource->getTableName($eavTable)],
                    ['attribute_id', 'store_id', 'value']
                )->where($linkField . ' = ?', $source->getData($linkField))
                ->where('store_id <> ?', Store::DEFAULT_STORE_ID);
            $records = $connection->fetchAll($select);

            if (!count($records)) {
                continue;
            }

            foreach ($records as $index => $bind) {
                $bind[$linkField] = $target->getData($linkField);
                $records[$index] = $bind;
            }

            $connection->insertMultiple($this->resource->getTableName($eavTable), $records);
        }
    }
}
