<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Helper;

use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Image;

/**
 * Class Data
 * Helper class for getting options
 *
 */
class Data
{
    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var UrlBuilder
     */
    private $imageUrlBuilder;

    /**
     * @param ImageHelper $imageHelper
     * @param UrlBuilder|null $imageUrlBuilder
     */
    public function __construct(ImageHelper $imageHelper)
    {
        $this->imageHelper = $imageHelper;
    }

    /**
     * Retrieve collection of gallery images
     *
     * @param ProductInterface $product
     * @return Image[]|null
     */
    public function getGalleryImages(ProductInterface $product)
    {
        $images = $product->getMediaGalleryImages();
        if ($images instanceof \Magento\Framework\Data\Collection) {
            /** @var $image Image */
            foreach ($images as $image) {
                $smallImageUrl = $this->getImageUrlBuilder()
                    ->getUrl($image->getFile(), 'product_page_image_small');
                $image->setData('small_image_url', $smallImageUrl);

                $mediumImageUrl = $this->getImageUrlBuilder()
                    ->getUrl($image->getFile(), 'product_page_image_medium_no_frame');
                $image->setData('medium_image_url', $mediumImageUrl);

                $largeImageUrl = $this->getImageUrlBuilder()
                    ->getUrl($image->getFile(), 'product_page_image_large_no_frame');
                $image->setData('large_image_url', $largeImageUrl);
            }
        }

        return $images;
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
            if ($images) {
                foreach ($images as $image) {
                    $options['images'][$productId][] =
                        [
                            'thumb' => $image->getData('small_image_url'),
                            'img' => $image->getData('medium_image_url'),
                            'full' => $image->getData('large_image_url'),
                            'caption' => $image->getLabel(),
                            'position' => $image->getPosition(),
                            'isMain' => $image->getFile() == $product->getImage(),
                        ];
                }
            }
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
     * Get allowed attributes
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAllowAttributes($product)
    {
        return $product->getTypeInstance()->getConfigurableAttributes($product);
    }

    /**
     * @return UrlBuilder
     * @deprecated
     */
    private function getImageUrlBuilder()
    {
        if (!$this->imageUrlBuilder) {
            $this->imageUrlBuilder = ObjectManager::getInstance()->get(UrlBuilder::class);
        }
        return $this->imageUrlBuilder;
    }
}
