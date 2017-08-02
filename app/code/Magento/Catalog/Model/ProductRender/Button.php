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
 * @inheritdoc
 * @since 2.2.0
 */
class Button extends \Magento\Framework\Model\AbstractExtensibleModel implements ButtonInterface
{
    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setPostData($postData)
    {
        $this->setData('post_data', $postData);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getPostData()
    {
        return $this->getData('post_data');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setUrl($url)
    {
        $this->setData('url', $url);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getUrl()
    {
        return $this->getData('url');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setRequiredOptions($requiredOptions)
    {
        $this->setData('required_options', $requiredOptions);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function hasRequiredOptions()
    {
        return $this->getData('required_options');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setExtensionAttributes(ButtonExtensionInterface $extensionAttributes)
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
