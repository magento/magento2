<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $params = array('id' => $this->getProduct()->getId());
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
        if ($whiteBorders) {
            $img->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false);
        }
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
