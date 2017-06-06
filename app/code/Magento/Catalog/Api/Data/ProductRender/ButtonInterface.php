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
interface ButtonInterface extends ExtensibleDataInterface
{
    /**
     * @param string $postData
     * @return void
     */
    public function setPostData($postData);

    /**
     * Retrieve post data
     *
     * @return string
     */
    public function getPostData();

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
     * @param string $requiredOptions
     * @return void
     */
    public function setRequiredOptions($requiredOptions);

    /**
     * @return string
     */
    public function getRequiredOptions();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\ButtonExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductRender\ButtonExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductRender\ButtonExtensionInterface $extensionAttributes
    );
}
