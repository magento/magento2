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
        $product = $this->getProduct();
        $images = $product->getMediaGalleryImages();
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as &$image) {
                /* @var \Magento\Framework\DataObject $image */
                $image->setData(
                    'small_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_small')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'medium_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_medium')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'large_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_large')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
            }
        }
        return $images;
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
     * @param null|\Magento\Framework\DataObject $image
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
     * Is product main image
     *
     * @param \Magento\Framework\DataObject $image
     * @return bool
     */
    public function isMainImage($image)
    {
        $product = $this->getProduct();
        return $product->getImage() == $image->getFile();
    }
}
