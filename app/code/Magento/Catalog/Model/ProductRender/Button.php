<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductRender;

use Magento\Catalog\Api\Data\ProductRender\AddToCartInterface;
use Magento\Catalog\Api\Data\ProductRender\ButtonInterface;
use Magento\Catalog\Api\Data\ProductRender\ButtonExtensionInterface;

/**
 * Price interface.
 * @api
 */
class Button extends \Magento\Framework\Model\AbstractExtensibleModel implements ButtonInterface
{
    /**
     * @param string $postData
     * @return void
     */
    public function setPostData($postData)
    {
        $this->setData('post_data', $postData);
    }

    /**
     * Retrieve post data array
     *
     * @return array
     */
    public function getPostData()
    {
        return $this->getData('post_data');
    }

    /**
     * @param string $url
     * @return @return void
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
     * @param string $requiredOptions
     * @return void
     */
    public function setRequiredOptions($requiredOptions)
    {
        $this->setData('required_options', $requiredOptions);
    }

    /**
     * @return string
     */
    public function getRequiredOptions()
    {
        return $this->getData('required_options');
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(ButtonExtensionInterface $extensionAttributes)
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
