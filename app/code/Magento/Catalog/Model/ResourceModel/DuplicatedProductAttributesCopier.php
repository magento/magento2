<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
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
     * Copy non-global attributes from source to target.
     *
     * @param Product $source
     * @param Product $target
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
        $mediaAttributeIds = [];
        foreach ($attributeCollection->getItems() as $item) {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $item */
            $eavTableNames[] = $item->getBackendTable();
            if ($item->getFrontendInput() === 'media_image') {
                $mediaAttributeIds[(int) $item->getAttributeId()] = true;
            }
        }

        $mediaFileMap = $this->getMediaFileMap($target);

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
                if (isset($mediaAttributeIds[(int) $bind['attribute_id']])) {
                    $bind['value'] = $mediaFileMap[$bind['value']] ?? $bind['value'];
                }
                $bind[$linkField] = $target->getData($linkField);
                $records[$index] = $bind;
            }

            $connection->insertMultiple($this->resource->getTableName($eavTable), $records);
        }
    }

    /**
     * Map media attribute values to the duplicate's new file names.
     *
     * @param Product $target
     * @return array
     */
    private function getMediaFileMap(Product $target): array
    {
        $mediaGallery = $target->getData('media_gallery');
        $fileMap = [];
        if (is_array($mediaGallery) && isset($mediaGallery['images']) && is_array($mediaGallery['images'])) {
            foreach ($mediaGallery['images'] as $image) {
                if (isset($image['file'], $image['new_file'])) {
                    $fileMap[$image['file']] = $image['new_file'];
                }
            }
        }
        return $fileMap;
    }
}
