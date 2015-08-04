<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Simple product data view
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Product\View;

use Magento\Framework\Data\Collection;

class Gallery extends \Magento\Catalog\Block\Product\View\AbstractView
{
    /**
     * Retrieve collection of gallery images
     *
     * @return Collection
     */
    public function getGalleryImages()
    {
        return $this->getProduct()->getMediaGalleryImages();
    }

    /**
     * Retrieve product images in JSON format
     *
     * @return string
     */
    public function getGalleryImagesJson()
    {
        $imagesItems = [];

        $imageWidth = $this->getVar("product_page_main_image:width");
        $imageHeight = $this->getVar("product_page_main_image:height") ?: $imageWidth;
        $whiteBorders =  $this->getVar("product_image_white_borders");
        $thumbWidth =  $this->getVar("product_page_more_views:width");
        $thumbHeight =  $this->getVar("product_page_more_views:height") ?: $thumbWidth;

        foreach ($this->getProduct()->getMediaGalleryImages() as $image) {
            $imageSmall = $this->getImageUrl($image, 'thumbnail', $whiteBorders, $thumbWidth, $thumbHeight);
            $imageMedium = $this->getImageUrl($image, 'image', $whiteBorders, $imageWidth, $imageHeight);

            $imagesItems[] = [
                'img' => $imageMedium,
                'thumb' => $imageSmall,
                'caption' => $image->getLabel(),
                'position' => $image->getPosition(),
            ];
        }
        return json_encode($imagesItems);
    }

    /**
     * Retrieve gallery url
     *
     * @param null|\Magento\Framework\Object $image
     * @return string
     */
    public function getGalleryUrl($image = null)
    {
        $params = ['id' => $this->getProduct()->getId()];
        if ($image) {
            $params['image'] = $image->getValueId();
        }
        return $this->getUrl('catalog/product/gallery', $params);
    }

    /**
     * Get gallery image url
     *
     * @param \Magento\Framework\Object $image
     * @param string $type
     * @param boolean $whiteBorders
     * @param null|number $width
     * @param null|number $height
     * @return string
     */
    public function getImageUrl($image, $type, $whiteBorders = false, $width = null, $height = null)
    {
        $product = $this->getProduct();
        $img = $this->_imageHelper->init($product, $type, $image->getFile());
        $img->constrainOnly(true)->keepAspectRatio(true)->keepFrame($whiteBorders);
        if ($width || $height) {
            $img->resize($width, $height);
        }
        return (string)$img;
    }

    /**
     * Is product main image
     *
     * @param \Magento\Framework\Object $image
     * @return bool
     */
    public function isMainImage($image)
    {
        $product = $this->getProduct();
        return $product->getImage() == $image->getFile();
    }
}
