<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Model\Plugin\Catalog\Product\Gallery;

/**
 * Abstract class for catalog product gallery handlers plugins.
 * @since 2.1.0
 */
abstract class AbstractHandler
{
    /**
     * @var array
     * @since 2.1.0
     */
    protected $videoPropertiesDbMapping = [
        'value_id' => 'value_id',
        'store_id' => 'store_id',
        'video_provider' => 'provider',
        'video_url' => 'url',
        'video_title' => 'title',
        'video_description' => 'description',
        'video_metadata' => 'metadata'
    ];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Gallery
     * @since 2.1.0
     */
    protected $resourceModel;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Gallery $resourceModel
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $resourceModel
    ) {
        $this->resourceModel = $resourceModel;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return array
     * @since 2.1.0
     */
    protected function getMediaEntriesDataCollection(
        \Magento\Catalog\Model\Product $product,
        \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
    ) {
        $attributeCode = $attribute->getAttributeCode();
        $mediaData = $product->getData($attributeCode);
        if (!empty($mediaData['images']) && is_array($mediaData['images'])) {
            return $mediaData['images'];
        }
        return [];
    }
}
