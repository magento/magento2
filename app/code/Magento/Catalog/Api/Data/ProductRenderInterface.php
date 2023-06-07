<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

use Magento\Catalog\Api\Data\ProductRender\ButtonInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Represents Data Object which holds enough information to render product
 * This information is put into part as Add To Cart or Add to Compare Data or Price Data
 *
 * @api
 * @since 102.0.0
 */
interface ProductRenderInterface extends ExtensibleDataInterface
{
    /**
     * Provide information needed for render "Add To Cart" button on front
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\ButtonInterface
     * @since 102.0.0
     */
    public function getAddToCartButton();

    /**
     * Set information needed for render "Add To Cart" button on front
     *
     * @param ButtonInterface $cartAddToCartButton
     * @return void
     * @since 102.0.0
     */
    public function setAddToCartButton(ButtonInterface $cartAddToCartButton);

    /**
     * Provide information needed for render "Add To Compare" button on front
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\ButtonInterface
     * @since 102.0.0
     */
    public function getAddToCompareButton();

    /**
     * Set information needed for render "Add To Compare" button on front
     *
     * @param ButtonInterface $compareButton
     * @return string
     * @since 102.0.0
     */
    public function setAddToCompareButton(ButtonInterface $compareButton);

    /**
     * Provide information needed for render prices and adjustments for different product types on front
     *
     * Prices are represented in raw format and in current currency
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface
     * @since 102.0.0
     */
    public function getPriceInfo();

    /**
     * Set information needed for render prices and adjustments for different product types on front
     *
     * @param \Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface $priceInfo
     * @return void
     * @since 102.0.0
     */
    public function setPriceInfo(PriceInfoInterface $priceInfo);

    /**
     * Provide enough information, that needed to render image on front
     *
     * Images can be separated by image codes
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\ImageInterface[]
     * @since 102.0.0
     */
    public function getImages();

    /**
     * Set enough information, that needed to render image on front
     *
     * @param \Magento\Catalog\Api\Data\ProductRender\ImageInterface[] $images
     * @return void
     * @since 102.0.0
     */
    public function setImages(array $images);

    /**
     * Provide product url
     *
     * @return string
     * @since 102.0.0
     */
    public function getUrl();

    /**
     * Set product url
     *
     * @param string $url
     * @return void
     * @since 102.0.0
     */
    public function setUrl($url);

    /**
     * Provide product identifier
     *
     * @return int
     * @since 102.0.0
     */
    public function getId();

    /**
     * Set product identifier
     *
     * @param int $id
     * @return void
     * @since 102.0.0
     */
    public function setId($id);

    /**
     * Provide product name
     *
     * @return string
     * @since 102.0.0
     */
    public function getName();

    /**
     * Set product name
     *
     * @param string $name
     * @return void
     * @since 102.0.0
     */
    public function setName($name);

    /**
     * Provide product type. Such as bundle, grouped, simple, etc...
     *
     * @return string
     * @since 102.0.0
     */
    public function getType();

    /**
     * Set product type.
     *
     * @param string $productType
     * @return void
     * @since 102.0.0
     */
    public function setType($productType);

    /**
     * Provide information about product saleability (In Stock)
     *
     * @return string
     * @since 102.0.0
     */
    public function getIsSalable();

    /**
     * Set information about product saleability (Stock, other conditions)
     *
     * Is used to provide information to frontend JS renders
     * You can add plugin, in order to hide product on product page or product list on front
     *
     * @param string $isSalable
     * @return void
     * @since 102.0.0
     */
    public function setIsSalable($isSalable);

    /**
     * Provide information about current store id or requested store id
     *
     * Product should be assigned to provided store id
     * This setting affect store scope attributes
     *
     * @return int
     * @since 102.0.0
     */
    public function getStoreId();

    /**
     * Set current or desired store id to product
     *
     * @param int $storeId
     * @return void
     * @since 102.0.0
     */
    public function setStoreId($storeId);

    /**
     * Provide current or desired currency code to product
     *
     * This setting affect formatted prices*
     *
     * @return string
     * @since 102.0.0
     */
    public function getCurrencyCode();

    /**
     * Set current or desired currency code to product
     *
     * @param string $currencyCode
     * @return void
     * @since 102.0.0
     */
    public function setCurrencyCode($currencyCode);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductRenderExtensionInterface
     * @since 102.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductRenderExtensionInterface $extensionAttributes
     * @return $this
     * @since 102.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductRenderExtensionInterface $extensionAttributes
    );
}
