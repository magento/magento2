<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api\Data;

/**
 * Interface for store config
 *
 * @api
 * @since 100.0.2
 */
interface StoreConfigInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get store id
     *
     * @return int
     */
    public function getId();

    /**
     * Set store id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get store code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set store code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get website id of the store
     *
     * @return int
     */
    public function getWebsiteId();

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);

    /**
     * Get store locale
     *
     * @return string
     */
    public function getLocale();

    /**
     * Set store locale
     *
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale);

    /**
     * Get base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode();

    /**
     * Set base currency code
     *
     * @param string $baseCurrencyCode
     * @return $this
     */
    public function setBaseCurrencyCode($baseCurrencyCode);

    /**
     * Get default display currency code
     *
     * @return string
     */
    public function getDefaultDisplayCurrencyCode();

    /**
     * Set default display currency code
     *
     * @param string $defaultDisplayCurrencyCode
     * @return $this
     */
    public function setDefaultDisplayCurrencyCode($defaultDisplayCurrencyCode);

    /**
     * Get timezone of the store
     *
     * @return string
     */
    public function getTimezone();

    /**
     * Set timezone of the store
     *
     * @param string $timezone
     * @return $this
     */
    public function setTimezone($timezone);

    /**
     * Return the unit of weight
     *
     * @return string
     */
    public function getWeightUnit();

    /**
     * Set the unit of weight
     *
     * @param string $weightUnit
     * @return $this
     */
    public function setWeightUnit($weightUnit);

    /**
     * Get base URL for the store
     *
     * @return string
     */
    public function getBaseUrl();

    /**
     * Set base URL
     *
     * @param string $baseUrl
     * @return $this
     */
    public function setBaseUrl($baseUrl);

    /**
     * Get base link URL for the store
     *
     * @return string
     */
    public function getBaseLinkUrl();

    /**
     * Set base link URL for the store
     *
     * @param string $baseLinkUrl
     * @return $this
     */
    public function setBaseLinkUrl($baseLinkUrl);

    /**
     * Get base static URL for the store
     *
     * @return string
     */
    public function getBaseStaticUrl();

    /**
     * Set base static URL for the store
     *
     * @param string $baseStaticUrl
     * @return $this
     */
    public function setBaseStaticUrl($baseStaticUrl);

    /**
     * Get base media URL for the store
     *
     * @return string
     */
    public function getBaseMediaUrl();

    /**
     * Set base media URL for the store
     *
     * @param string $baseMediaUrl
     * @return $this
     */
    public function setBaseMediaUrl($baseMediaUrl);

    /**
     * Get secure base URL for the store
     *
     * @return string
     */
    public function getSecureBaseUrl();

    /**
     * Set secure base URL
     *
     * @param string $secureBaseUrl
     * @return $this
     */
    public function setSecureBaseUrl($secureBaseUrl);

    /**
     * Get secure base link URL for the store
     *
     * @return string
     */
    public function getSecureBaseLinkUrl();

    /**
     * Set secure base link URL for the store
     *
     * @param string $secureBaseLinkUrl
     * @return $this
     */
    public function setSecureBaseLinkUrl($secureBaseLinkUrl);

    /**
     * Get secure base static URL for the store
     *
     * @return string
     */
    public function getSecureBaseStaticUrl();

    /**
     * Set secure base static URL for the store
     *
     * @param string $secureBaseStaticUrl
     * @return $this
     */
    public function setSecureBaseStaticUrl($secureBaseStaticUrl);

    /**
     * Get secure base media URL for the store
     *
     * @return string
     */
    public function getSecureBaseMediaUrl();

    /**
     * Set secure base media URL for the store
     *
     * @param string $secureBaseMediaUrl
     * @return $this
     */
    public function setSecureBaseMediaUrl($secureBaseMediaUrl);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Store\Api\Data\StoreConfigExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Store\Api\Data\StoreConfigExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Store\Api\Data\StoreConfigExtensionInterface $extensionAttributes
    );
}
