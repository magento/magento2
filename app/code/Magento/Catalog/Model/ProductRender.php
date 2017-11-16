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
 */
class ProductRender extends \Magento\Framework\Model\AbstractExtensibleModel implements ProductRenderInterface
{
    /**
     * @inheritdoc
     */
    public function getAddToCartButton()
    {
        return $this->getData('add_to_cart_button');
    }

    /**
     * @inheritdoc
     */
    public function setAddToCartButton(ButtonInterface $addToCartButton)
    {
        $this->setData('add_to_cart_button', $addToCartButton);
    }

    /**
     * @inheritdoc
     */
    public function getAddToCompareButton()
    {
        return $this->getData('add_to_compare_button');
    }

    /**
     * @inheritdoc
     */
    public function setAddToCompareButton(ButtonInterface $compareButton)
    {
        $this->setData('add_to_compare_button', $compareButton);
    }

    /**
     * @inheritdoc
     */
    public function getPriceInfo()
    {
        return $this->getData('price_info');
    }

    /**
     * @inheritdoc
     */
    public function setPriceInfo(PriceInfoInterface $priceInfo)
    {
        $this->setData('price_info', $priceInfo);
    }

    /**
     * @inheritdoc
     */
    public function getImages()
    {
        return $this->getData('images');
    }

    /**
     * @inheritdoc
     */
    public function setImages(array $images)
    {
        $this->setData('images', $images);
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
    public function setUrl($url)
    {
        $this->setData('url', $url);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        $this->setData('id', $id);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData('name', $name);
    }

    /**
     * @inheritdoc
     */
    public function setType($productType)
    {
        $this->setData('type', $productType);
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->getData("type");
    }

    /**
     * @inheritdoc
     */
    public function getIsSalable()
    {
        return $this->getData("is_salable");
    }

    /**
     * @inheritdoc
     */
    public function setIsSalable($isSalable)
    {
        $this->setData('is_salable', $isSalable);
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId)
    {
        $this->setData('store_id', $storeId);
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    /**
     * @inheritdoc
     */
    public function getCurrencyCode()
    {
        return $this->getData('currency_code');
    }

    /**
     * @inheritdoc
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->setData('currency_code', $currencyCode);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductRenderExtensionInterface
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
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductRenderExtensionInterface $extensionAttributes
    ) {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
