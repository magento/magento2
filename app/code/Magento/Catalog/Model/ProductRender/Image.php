<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductRender;

use Magento\Catalog\Api\Data\ProductRender\ImageInterface;

/**
 * Product image renderer model.
 */
class Image extends \Magento\Framework\Model\AbstractExtensibleModel implements
    ImageInterface
{
    /**
     * Set url to image.
     *
     * @param string $url
     * @return void
     */
    public function setUrl($url)
    {
        $this->setData('url', $url);
    }

    /**
     * Retrieve url, needed to add product to cart
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getData('url');
    }

    /**
     * Retrieve image code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getData('code');
    }

    /**
     * Set image code.
     *
     * @param string $code
     * @return void
     */
    public function setCode($code)
    {
        $this->setData('code', $code);
    }

    /**
     * Set image height.
     *
     * @param string $height
     * @return void
     */
    public function setHeight($height)
    {
        $this->setData('height', $height);
    }

    /**
     * Retrieve image height.
     *
     * @return float
     */
    public function getHeight()
    {
        return $this->getData('height');
    }

    /**
     * Retrieve image width.
     *
     * @return float
     */
    public function getWidth()
    {
        return $this->getData('width');
    }

    /**
     * Set image width.
     *
     * @param string $width
     * @return void
     */
    public function setWidth($width)
    {
        $this->setData('width', $width);
    }

    /**
     * Retrieve image label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->getData('label');
    }

    /**
     * Set image label.
     *
     * @param string $label
     * @return void
     */
    public function setLabel($label)
    {
        $this->setData('label', $label);
    }

    /**
     * Retrieve image width after image resize.
     *
     * @return float
     */
    public function getResizedWidth()
    {
        return $this->getData('resized_width');
    }

    /**
     * Set image width after image resize.
     *
     * @param string $width
     * @return void
     */
    public function setResizedWidth($width)
    {
        $this->setData('resized_width', $width);
    }

    /**
     * Set image height after image resize.
     *
     * @param string $height
     * @return void
     */
    public function setResizedHeight($height)
    {
        $this->setData('resized_height', $height);
    }

    /**
     * Retrieve image height after image resize.
     *
     * @return float
     */
    public function getResizedHeight()
    {
        return $this->getData('resized_height');
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\ImageExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductRender\ImageExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductRender\ImageExtensionInterface $extensionAttributes
    ) {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
