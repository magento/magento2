<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Backend\Media;

use Magento\Catalog\Model\Product;
use \Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Class ImageMediaGalleryEntryProcessor
 */
class ImageMediaGalleryEntryProcessor extends AbstractMediaGalleryEntryProcessor
{
    /**
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function afterLoad(Product $product, AbstractAttribute $attribute)
    {
        $attrCode = $attribute->getAttributeCode();
        $value = [];
        $value['images'] = [];
        $value['values'] = [];
        $localAttributes = ['label', 'position', 'disabled'];

        $mediaEntries = $this->resourceEntryMediaGallery
            ->loadProductGalleryByAttributeId($product, $attribute->getId());
        foreach ($mediaEntries as $mediaEntry) {
            foreach ($localAttributes as $localAttribute) {
                if ($mediaEntry[$localAttribute] === null) {
                    $mediaEntry[$localAttribute] = $this->getDefaultValue($localAttribute, $mediaEntry);
                }
            }
            $value['images'][] = $mediaEntry;
        }

        $product->setData($attrCode, $value);
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
     * @param string $key
     * @param string[] &$image
     * @return string
     */
    protected function getDefaultValue($key, &$image)
    {
        if (isset($image[$key . '_default'])) {
            return $image[$key . '_default'];
        }

        return '';
    }
}
