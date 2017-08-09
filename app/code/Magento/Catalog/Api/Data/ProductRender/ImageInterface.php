<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data\ProductRender;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Product Render image interface.
 *
 * Represents physical characteristics of image, that can be used in product listing or product view
 *
 * @api
 */
interface ImageInterface extends ExtensibleDataInterface
{
    /**
     * Set source or external url to the image
     * (attribute src)
     *
     * @param string $url
     * @return void
     */
    public function setUrl($url);

    /**
     * Retrieve image url
     *
     * @return string
     */
    public function getUrl();

    /**
     * Retrieve image code
     *
     * Image code shows, where this image can be used: on listing or on view,
     * What size should this image have, etc...
     *
     * @return string
     */
    public function getCode();

    /**
     * Set image code
     *
     * @param string $code
     * @return void
     */
    public function setCode($code);

    /**
     * Set original image height in px, e.g. 212.21 px
     *
     * @param string $height
     * @return void
     */
    public function setHeight($height);

    /**
     * Retrieve image height
     *
     * @return float
     */
    public function getHeight();

    /**
     * Set image width in px
     *
     * @return float
     */
    public function getWidth();

    /**
     * Set original image width
     *
     * @param string $width
     * @return void
     */
    public function setWidth($width);

    /**
     * Retrieve image label
     * Image label is short description of this image
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set image label
     *
     * @param string $label
     * @return void
     */
    public function setLabel($label);

    /**
     * Retrieve resize width
     *
     * This width is image dimension, which represents the width, that can be used for perfomance improvements
     *
     * @return float
     */
    public function getResizedWidth();

    /**
     * Set resized width
     *
     * @param string $width
     * @return void
     */
    public function setResizedWidth($width);

    /**
     * @param string $height
     * @return void
     */
    public function setResizedHeight($height);

    /**
     * Retrieve resize height
     *
     * @return float
     */
    public function getResizedHeight();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\ImageExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductRender\ImageExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductRender\ImageExtensionInterface $extensionAttributes
    );
}
