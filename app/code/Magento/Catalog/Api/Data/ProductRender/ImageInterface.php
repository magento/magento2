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
 * @since 102.0.0
 */
interface ImageInterface extends ExtensibleDataInterface
{
    /**
     * Set source or external url to the image
     * (attribute src)
     *
     * @param string $url
     * @return void
     * @since 102.0.0
     */
    public function setUrl($url);

    /**
     * Retrieve image url
     *
     * @return string
     * @since 102.0.0
     */
    public function getUrl();

    /**
     * Retrieve image code
     *
     * Image code shows, where this image can be used: on listing or on view,
     * What size should this image have, etc...
     *
     * @return string
     * @since 102.0.0
     */
    public function getCode();

    /**
     * Set image code
     *
     * @param string $code
     * @return void
     * @since 102.0.0
     */
    public function setCode($code);

    /**
     * Set original image height in px, e.g. 212.21 px
     *
     * @param string $height
     * @return void
     * @since 102.0.0
     */
    public function setHeight($height);

    /**
     * Retrieve image height
     *
     * @return float
     * @since 102.0.0
     */
    public function getHeight();

    /**
     * Set image width in px
     *
     * @return float
     * @since 102.0.0
     */
    public function getWidth();

    /**
     * Set original image width
     *
     * @param string $width
     * @return void
     * @since 102.0.0
     */
    public function setWidth($width);

    /**
     * Retrieve image label
     *
     * Image label is short description of this image
     *
     * @return string
     * @since 102.0.0
     */
    public function getLabel();

    /**
     * Set image label
     *
     * @param string $label
     * @return void
     * @since 102.0.0
     */
    public function setLabel($label);

    /**
     * Retrieve resize width
     *
     * This width is image dimension, which represents the width, that can be used for performance improvements
     *
     * @return float
     * @since 102.0.0
     */
    public function getResizedWidth();

    /**
     * Set resized width
     *
     * @param string $width
     * @return void
     * @since 102.0.0
     */
    public function setResizedWidth($width);

    /**
     * Set resized height
     *
     * @param string $height
     * @return void
     * @since 102.0.0
     */
    public function setResizedHeight($height);

    /**
     * Retrieve resize height
     *
     * @return float
     * @since 102.0.0
     */
    public function getResizedHeight();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\ImageExtensionInterface|null
     * @since 102.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductRender\ImageExtensionInterface $extensionAttributes
     * @return $this
     * @since 102.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductRender\ImageExtensionInterface $extensionAttributes
    );
}
