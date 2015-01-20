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
     * Retrieve list of gallery images
     *
     * @return array|Collection
     */
    public function getGalleryImages()
    {
        return $this->getProduct()->getMediaGalleryImages();
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
