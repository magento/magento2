<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Model\Product\Attribute\Media;

use Magento\Catalog\Model\Product\Attribute\Backend\Media\AbstractMediaGalleryEntryProcessor;
use \Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Catalog\Model\Product;

/**
 * Class ImageMediaGalleryEntryProcessor
 */
class ExternalVideoMediaGalleryEntryProcessor extends AbstractMediaGalleryEntryProcessor
{
    const GALLERY_VALUE_VIDEO_TABLE = 'catalog_product_entity_media_gallery_value_video';

    protected $videoProperties = [
        'video_value_id',
        'video_provider',
        'video_url',
        'video_title',
        'video_description',
        'video_metadata'
    ];

    /**
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function afterLoad(Product $product, AbstractAttribute $attribute)
    {
        $attributeCode = $attribute->getAttributeCode();
        $mediaCollection = $this->getMediaEntriesDataCollection($product->getData($attributeCode));
        if (!empty($mediaCollection)) {
            $ids = $this->collectVideoEntriesIds($mediaCollection);
            $videoDataCollection = $this->loadVideoDataById($ids);
            $mediaEntriesDataCollection = $this->addVideoDataToMediaEntries($mediaCollection, $videoDataCollection);
            $product->setData($attributeCode, $mediaEntriesDataCollection);
        }
    }

    /**
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function beforeSave(Product $product, AbstractAttribute $attribute)
    {

    }

    /**
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function afterSave(Product $product, AbstractAttribute $attribute)
    {

    }

    /**
     * @param array $mediaData
     * @return array
     */
    protected function getMediaEntriesDataCollection(array $mediaData)
    {
        if (is_array($mediaData['images'])) {
            return $mediaData['images'];
        }
        return [];
    }

    /**
     * @param array $mediaCollection
     * @return array
     */
    protected function collectVideoEntriesIds(array $mediaCollection)
    {
        $ids = [];
        foreach ($mediaCollection as $item) {
            if ($item['media_type'] == ExternalVideoMediaEntryConverter::MEDIA_TYPE_CODE) {
                $ids[] = $item['value_id'];
            }
        }
        return $ids;
    }

    protected function loadVideoDataById(array $ids)
    {
        foreach ($ids as $id) {

        }

    }

    protected function addVideoDataToMediaEntries(array $mediaCollection, array $data)
    {
        return ['image' => $mediaCollection];
    }
}
