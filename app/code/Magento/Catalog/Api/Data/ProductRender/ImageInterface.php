<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data\ProductRender;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Button interface.
 * @api
 */
interface ImageInterface extends ExtensibleDataInterface
{
    /**
     * @param string $url
     * @return void
     */
    public function setUrl($url);

    /**
     * Retrieve url, needed to add product to cart
     *
     * @return string
     */
    public function getUrl();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     * @return void
     */
    public function setCode($code);

    /**
     * @param string $height
     * @return void
     */
    public function setHeight($height);

    /**
     * @return float
     */
    public function getHeight();

    /**
     * @return float
     */
    public function getWidth();

    /**
     * @param string $width
     * @return void
     */
    public function setWidth($width);

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string $label
     * @return void
     */
    public function setLabel($label);

    /**
     * @return float
     */
    public function getResizedWidth();

    /**
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
