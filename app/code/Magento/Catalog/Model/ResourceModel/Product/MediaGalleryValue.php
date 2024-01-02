<?php
/************************************************************************
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;

class MediaGalleryValue
{
    /**
     * @param Gallery $galleryResource
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        private readonly Gallery $galleryResource,
        private readonly MetadataPool $metadataPool
    ) {
    }

    /**
     * Retrieve all gallery values for entity.
     *
     * @param int $entityId
     * @return array
     * @throws \Exception
     */
    public function getAllByEntityId(int $entityId): array
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $connection = $this->galleryResource->getConnection();
        $select = $connection->select()
            ->from($this->galleryResource->getTable(Gallery::GALLERY_VALUE_TABLE))
            ->where($metadata->getLinkField() . ' = ?', $entityId);

        return $connection->fetchAll($select);
    }

    /**
     * Create or update media gallery value record.
     *
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function saveGalleryStoreValue(array $data): void
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $connection = $this->galleryResource->getConnection();
        $fields = $connection->describeTable($this->galleryResource->getTable(Gallery::GALLERY_VALUE_TABLE));
        if (isset($data['record_id'])) {
            $id = (int) $data['record_id'];
            $data = array_intersect_key($data, $fields);
            unset($data['record_id'], $data['value_id'], $data['store_id'], $data[$metadata->getLinkField()]);
            $connection->update(
                $this->galleryResource->getTable(Gallery::GALLERY_VALUE_TABLE),
                $data,
                [
                    'record_id = ?' => $id,
                ]
            );
        } else {
            $this->galleryResource->insertGalleryValueInStore($data);
        }
    }
}
