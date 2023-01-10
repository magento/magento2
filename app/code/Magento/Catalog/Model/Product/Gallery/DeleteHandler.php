<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Eav\Model\ResourceModel\AttributeValue;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Delete all media gallery records for provided product
 */
class DeleteHandler implements ExtensionInterface
{
    /**
     * @var EntityMetadata
     */
    private $metadata;

    /**
     * @var Gallery
     */
    private $galleryResourceModel;

    /**
     * @var AttributeValue
     */
    private $attributeValue;

    /**
     * @param MetadataPool $metadataPool
     * @param Gallery $galleryResourceModel
     * @param AttributeValue $attributeValue
     */
    public function __construct(
        MetadataPool $metadataPool,
        Gallery $galleryResourceModel,
        AttributeValue $attributeValue
    ) {
        $this->metadata = $metadataPool->getMetadata(ProductInterface::class);
        $this->galleryResourceModel = $galleryResourceModel;
        $this->attributeValue = $attributeValue;
    }

    /**
     * Delete all media gallery records for provided product
     *
     * @param Product $product
     * @param array $arguments
     * @return void
     */
    public function execute($product, $arguments = []): void
    {
        $valuesId = $this->getMediaGalleryValuesId($product);
        if ($valuesId) {
            $this->galleryResourceModel->deleteGallery($valuesId);
        }
        if (isset($arguments['media_attribute_codes'])) {
            $values = $this->attributeValue->getValues(
                ProductInterface::class,
                (int) $product->getData($this->metadata->getLinkField()),
                $arguments['media_attribute_codes']
            );
            if ($values) {
                $this->attributeValue->deleteValues(
                    ProductInterface::class,
                    $values
                );
            }
        }
    }

    /**
     * Get product media gallery values IDs
     *
     * @param Product $product
     * @return array
     */
    private function getMediaGalleryValuesId(Product $product): array
    {
        $connection = $this->galleryResourceModel->getConnection();
        $select = $connection->select()
            ->from($this->galleryResourceModel->getTable(Gallery::GALLERY_VALUE_TO_ENTITY_TABLE))
            ->where(
                $this->metadata->getLinkField() . '=?',
                $product->getData($this->metadata->getLinkField()),
                \Zend_Db::INT_TYPE
            );
        return $connection->fetchCol($select);
    }
}
