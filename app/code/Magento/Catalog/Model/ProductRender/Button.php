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
 */
class Button extends \Magento\Framework\Model\AbstractExtensibleModel implements ButtonInterface
{
    /**
     * @inheritdoc
     */
    public function setPostData($postData)
    {
        $this->setData('post_data', $postData);
    }

    /**
     * @inheritdoc
     */
    public function getPostData()
    {
        return $this->getData('post_data');
    }

    /**
     * @inheritdoc
     */
    public function setUrl($url)
    {
        $this->setData('url', $url);
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->getData('url');
    }

    /**
     * @inheritdoc
     */
    public function setRequiredOptions($requiredOptions)
    {
        $this->setData('required_options', $requiredOptions);
    }

    /**
     * @inheritdoc
     */
    public function hasRequiredOptions()
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
