<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Helper;

use Magento\Catalog\Model\Product;

/**
 * Class Data
 * Helper class for getting options
 *
 */
class Data
{
    /**
     * Catalog Image Helper
     *
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @param \Magento\Catalog\Helper\Image $imageHelper
     */
    public function __construct(\Magento\Catalog\Helper\Image $imageHelper)
    {
        $this->imageHelper = $imageHelper;
    }

    /**
     * Get Options for Configurable Product Options
     *
     * @param \Magento\Catalog\Model\Product $currentProduct
     * @param array $allowedProducts
     * @return array
     */
    public function getOptions($currentProduct, $allowedProducts)
    {
        $options = [];
        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            $images = $this->getGalleryImages($product);
            $options['images'][$productId] = $images;
            foreach ($this->getAllowAttributes($currentProduct) as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());

                $options[$productAttributeId][$attributeValue][] = $productId;
                $options['index'][$productId][$productAttributeId] = $attributeValue;
            }
        }
        return $options;
    }

    /**
     * Retrieve collection of gallery images
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Catalog\Model\Product\Image[]|null
     */
    public function getGalleryImages(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $images = [];
        $imagesGallery = $product->getMediaGalleryImages();
        if ($imagesGallery instanceof \Magento\Framework\Data\Collection) {
            foreach ($imagesGallery as $image) {
                /** @var $image \Magento\Catalog\Model\Product\Image */
                $images[] = (string)$this->imageHelper->init($product, 'image')->setImageFile($image->getFile());
            }
        }

        return $images;
    }

    /**
     * Get allowed attributes
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAllowAttributes($product)
    {
        return $product->getTypeInstance()->getConfigurableAttributes($product);
    }
}
