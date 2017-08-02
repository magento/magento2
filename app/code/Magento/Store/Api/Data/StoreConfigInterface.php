<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api\Data;

/**
 * StoreConfig interface
 *
 * @api
 * @since 2.0.0
 */
interface StoreConfigInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get store id
     *
     * @return int
     * @since 2.0.0
     */
    public function getId();

    /**
     * Set store id
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * Get store code
     *
     * @return string
     * @since 2.0.0
     */
    public function getCode();

    /**
     * Set store code
     *
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code);

    /**
     * Get website id of the store
     *
     * @return int
     * @since 2.0.0
     */
    public function getWebsiteId();

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     * @since 2.0.0
     */
    public function setWebsiteId($websiteId);

    /**
     * Get store locale
     *
     * @return string
     * @since 2.0.0
     */
    public function getLocale();

    /**
     * Set store locale
     *
     * @param string $locale
     * @return $this
     * @since 2.0.0
     */
    public function setLocale($locale);

    /**
     * Get base currency code
     *
     * @return string
     * @since 2.0.0
     */
    public function getBaseCurrencyCode();

    /**
     * Set base currency code
     *
     * @param string $baseCurrencyCode
     * @return $this
     * @since 2.0.0
     */
    public function setBaseCurrencyCode($baseCurrencyCode);

    /**
     * Get default display currency code
     *
     * @return string
     * @since 2.0.0
     */
    public function getDefaultDisplayCurrencyCode();

    /**
     * Set default display currency code
     *
     * @param string $defaultDisplayCurrencyCode
     * @return $this
     * @since 2.0.0
     */
    public function setDefaultDisplayCurrencyCode($defaultDisplayCurrencyCode);

    /**
     * Get timezone of the store
     *
     * @return string
     * @since 2.0.0
     */
    public function getTimezone();

    /**
     * Set timezone of the store
     *
     * @param string $timezone
     * @return $this
     * @since 2.0.0
     */
    public function setTimezone($timezone);

    /**
     * Return the unit of weight
     *
     * @return string
     * @since 2.0.0
     */
    public function getWeightUnit();

    /**
     * Set the unit of weight
     *
     * @param string $weightUnit
     * @return $this
     * @since 2.0.0
     */
    public function setWeightUnit($weightUnit);

    /**
     * Get base URL for the store
     *
     * @return string
     * @since 2.0.0
     */
    public function getBaseUrl();

    /**
     * set base URL
     *
     * @param string $baseUrl
     * @return $this
     * @since 2.0.0
     */
    public function setBaseUrl($baseUrl);

    /**
     * Get base link URL for the store
     *
     * @return string
     * @since 2.0.0
     */
    public function getBaseLinkUrl();

    /**
     * Set base link URL for the store
     *
     * @param string $baseLinkUrl
     * @return $this
     * @since 2.0.0
     */
    public function setBaseLinkUrl($baseLinkUrl);

    /**
     * Get base static URL for the store
     *
     * @return string
     * @since 2.0.0
     */
    public function getBaseStaticUrl();

    /**
     * Set base static URL for the store
     *
     * @param string $baseStaticUrl
     * @return $this
     * @since 2.0.0
     */
    public function setBaseStaticUrl($baseStaticUrl);

    /**
     * Get base media URL for the store
     *
     * @return string
     * @since 2.0.0
     */
    public function getBaseMediaUrl();

    /**
     * Set base media URL for the store
     *
     * @param string $baseMediaUrl
     * @return $this
     * @since 2.0.0
     */
    public function setBaseMediaUrl($baseMediaUrl);

    /**
     * Get secure base URL for the store
     *
     * @return string
     * @since 2.0.0
     */
    public function getSecureBaseUrl();

    /**
     * set secure base URL
     *
     * @param string $secureBaseUrl
     * @return $this
     * @since 2.0.0
     */
    public function setSecureBaseUrl($secureBaseUrl);

    /**
     * Get secure base link URL for the store
     *
     * @return string
     * @since 2.0.0
     */
    public function getSecureBaseLinkUrl();

    /**
     * Set secure base link URL for the store
     *
     * @param string $secureBaseLinkUrl
     * @return $this
     * @since 2.0.0
     */
    public function setSecureBaseLinkUrl($secureBaseLinkUrl);

    /**
     * Get secure base static URL for the store
     *
     * @return string
     * @since 2.0.0
     */
    public function getSecureBaseStaticUrl();

    /**
     * Set secure base static URL for the store
     *
     * @param string $secureBaseStaticUrl
     * @return $this
     * @since 2.0.0
     */
    public function setSecureBaseStaticUrl($secureBaseStaticUrl);

    /**
     * Get secure base media URL for the store
     *
     * @return string
     * @since 2.0.0
     */
    public function getSecureBaseMediaUrl();

    /**
     * Set secure base media URL for the store
     *
     * @param string $secureBaseMediaUrl
     * @return $this
     * @since 2.0.0
     */
    public function setSecureBaseMediaUrl($secureBaseMediaUrl);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Store\Api\Data\StoreConfigExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Store\Api\Data\StoreConfigExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Store\Api\Data\StoreConfigExtensionInterface $extensionAttributes
    );
}
