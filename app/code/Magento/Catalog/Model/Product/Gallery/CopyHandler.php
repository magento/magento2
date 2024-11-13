<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Eav\Model\ResourceModel\AttributeValue;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Copy gallery data from one product to another
 */
class CopyHandler implements ExtensionInterface
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
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var AttributeValue
     */
    private $attributeValue;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var ProductAttributeInterface
     */
    private $attribute;

    /**
     * @param MetadataPool $metadataPool
     * @param Gallery $galleryResourceModel
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param AttributeValue $attributeValue
     * @param Json $json
     */
    public function __construct(
        MetadataPool $metadataPool,
        Gallery $galleryResourceModel,
        ProductAttributeRepositoryInterface $attributeRepository,
        AttributeValue $attributeValue,
        Json $json
    ) {
        $this->metadata = $metadataPool->getMetadata(ProductInterface::class);
        $this->galleryResourceModel = $galleryResourceModel;
        $this->attributeRepository = $attributeRepository;
        $this->attributeValue = $attributeValue;
        $this->json = $json;
    }

    /**
     * Copy gallery data from one product to another
     *
     * @param Product $product
     * @param array $arguments
     * @return void
     */
    public function execute($product, $arguments = []): void
    {
        $fromId = (int) $arguments['original_link_id'];
        $toId = $product->getData($this->metadata->getLinkField());
        $attributeId = $this->getAttribute()->getAttributeId();
        $valueIdMap = $this->galleryResourceModel->duplicate($attributeId, [], $fromId, $toId);
        $gallery = $this->getMediaGalleryCollection($product);

        if (!empty($gallery['images'])) {
            $images = [];
            foreach ($gallery['images'] as $key => $image) {
                $valueId = $image['value_id'] ?? null;
                $newKey = $key;
                if ($valueId !== null) {
                    $newValueId = $valueId;
                    if (isset($valueIdMap[$valueId])) {
                        $newValueId = $valueIdMap[$valueId];
                    }
                    if (((int) $valueId) === $key) {
                        $newKey = $newValueId;
                    }
                    $image['value_id'] = $newValueId;
                }
                $images[$newKey] = $image;
            }
            $gallery['images'] = $images;
            $attrCode = $this->getAttribute()->getAttributeCode();
            $product->setData($attrCode, $gallery);
        }

        //Copy media attribute values from one product to another
        if (isset($arguments['media_attribute_codes'])) {
            $values = $this->attributeValue->getValues(
                ProductInterface::class,
                $fromId,
                $arguments['media_attribute_codes']
            );
            if ($values) {
                foreach (array_keys($values) as $key) {
                    $values[$key][$this->metadata->getLinkField()] = $product->getData($this->metadata->getLinkField());
                    unset($values[$key]['value_id']);
                }
                $this->attributeValue->insertValues(
                    ProductInterface::class,
                    $values
                );
            }
        }
    }

    /**
     * Get product media gallery collection
     *
     * @param Product $product
     * @return array
     */
    private function getMediaGalleryCollection(Product $product): array
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $product->getData($attrCode);

        if (is_array($value) && isset($value['images'])) {
            if (!is_array($value['images']) && strlen($value['images']) > 0) {
                $value['images'] = $this->json->unserialize($value['images']);
            }

            if (!is_array($value['images'])) {
                $value['images'] = [];
            }
        }

        return $value;
    }

    /**
     * Returns media gallery attribute instance
     *
     * @return ProductAttributeInterface
     */
    private function getAttribute(): ProductAttributeInterface
    {
        if (!$this->attribute) {
            $this->attribute = $this->attributeRepository->get(
                ProductInterface::MEDIA_GALLERY
            );
        }

        return $this->attribute;
    }
}
