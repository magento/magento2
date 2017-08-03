<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductRender\ButtonInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface;
use Magento\Catalog\Api\Data\ProductRenderInterface;

/**
 * DTO which represents structure for product render information
 * @since 2.2.0
 */
class ProductRender extends \Magento\Framework\Model\AbstractExtensibleModel implements ProductRenderInterface
{
    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getAddToCartButton()
    {
        return $this->getData('add_to_cart_button');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setAddToCartButton(ButtonInterface $addToCartButton)
    {
        $this->setData('add_to_cart_button', $addToCartButton);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getAddToCompareButton()
    {
        return $this->getData('add_to_compare_button');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setAddToCompareButton(ButtonInterface $compareButton)
    {
        $this->setData('add_to_compare_button', $compareButton);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getPriceInfo()
    {
        return $this->getData('price_info');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setPriceInfo(PriceInfoInterface $priceInfo)
    {
        $this->setData('price_info', $priceInfo);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getImages()
    {
        return $this->getData('images');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setImages(array $images)
    {
        $this->setData('images', $images);
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
    public function setUrl($url)
    {
        $this->setData('url', $url);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setId($id)
    {
        $this->setData('id', $id);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setName($name)
    {
        $this->setData('name', $name);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setType($productType)
    {
        $this->setData('type', $productType);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getType()
    {
        return $this->getData("type");
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getIsSalable()
    {
        return $this->getData("is_salable");
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setIsSalable($isSalable)
    {
        $this->setData('is_salable', $isSalable);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setStoreId($storeId)
    {
        $this->setData('store_id', $storeId);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getCurrencyCode()
    {
        return $this->getData('currency_code');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->setData('currency_code', $currencyCode);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductRenderExtensionInterface
     * @since 2.2.0
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductRenderExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.2.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductRenderExtensionInterface $extensionAttributes
    ) {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
