<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Data;

/**
 * Allows to get and set store config values
 *
 * @codeCoverageIgnore
 */
class StoreConfig extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Store\Api\Data\StoreConfigInterface
{
    const KEY_ID = 'id';
    const KEY_CODE = 'code';
    const KEY_WEBSITE_ID = 'website_id';
    const KEY_LOCALE = 'locale';
    const KEY_BASE_CURRENCY_CODE = 'base_currency_code';
    const KEY_DEFAULT_DISPLAY_CURRENCY_CODE = 'default_display_currency_code';
    const KEY_TIMEZONE = 'timezone';
    const KEY_WEIGHT_UNIT = 'weight_unit';
    const KEY_BASE_URL = 'base_url';
    const KEY_BASE_LINK_URL = 'base_link_url';
    const KEY_BASE_STATIC_URL = 'base_static_url';
    const KEY_BASE_MEDIA_URL = 'base_media_url';
    const KEY_SECURE_BASE_URL = 'secure_base_url';
    const KEY_SECURE_BASE_LINK_URL = 'secure_base_link_url';
    const KEY_SECURE_BASE_STATIC_URL = 'secure_base_static_url';
    const KEY_SECURE_BASE_MEDIA_URL = 'secure_base_media_url';

    /**
     * Get store id
     *
     * @return int
     */
    public function getId()
    {
        return $this->_get(self::KEY_ID);
    }

    /**
     * Set store id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::KEY_ID, $id);
    }

    /**
     * Get store code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_get(self::KEY_CODE);
    }

    /**
     * Set store code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        return $this->setData(self::KEY_CODE, $code);
    }

    /**
     * Get website id of the store
     *
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->_get(self::KEY_WEBSITE_ID);
    }

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::KEY_WEBSITE_ID, $websiteId);
    }

    /**
     * Get store locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->_get(self::KEY_LOCALE);
    }

    /**
     * Set store locale
     *
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        return $this->setData(self::KEY_LOCALE, $locale);
    }

    /**
     * Get base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->_get(self::KEY_BASE_CURRENCY_CODE);
    }

    /**
     * Set base currency code
     *
     * @param string $baseCurrencyCode
     * @return $this
     */
    public function setBaseCurrencyCode($baseCurrencyCode)
    {
        return $this->setData(self::KEY_BASE_CURRENCY_CODE, $baseCurrencyCode);
    }

    /**
     * Get default display currency code
     *
     * @return string
     */
    public function getDefaultDisplayCurrencyCode()
    {
        return $this->_get(self::KEY_DEFAULT_DISPLAY_CURRENCY_CODE);
    }

    /**
     * Set default display currency code
     *
     * @param string $defaultDisplayCurrencyCode
     * @return $this
     */
    public function setDefaultDisplayCurrencyCode($defaultDisplayCurrencyCode)
    {
        return $this->setData(self::KEY_DEFAULT_DISPLAY_CURRENCY_CODE, $defaultDisplayCurrencyCode);
    }

    /**
     * Return the unit of weight
     *
     * @return string
     */
    public function getWeightUnit()
    {
        return $this->_get(self::KEY_WEIGHT_UNIT);
    }

    /**
     * Set the unit of weight
     *
     * @param string $weightUnit
     * @return $this
     */
    public function setWeightUnit($weightUnit)
    {
        return $this->setData(self::KEY_WEIGHT_UNIT, $weightUnit);
    }

    /**
     * Get base URL for the store
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_get(self::KEY_BASE_URL);
    }

    /**
     * Set base URL
     *
     * @param string $baseUrl
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        return $this->setData(self::KEY_BASE_URL, $baseUrl);
    }

    /**
     * Get base link URL for the store
     *
     * @return string
     */
    public function getBaseLinkUrl()
    {
        return $this->_get(self::KEY_BASE_LINK_URL);
    }

    /**
     * Get base link URL for the store
     *
     * @param string $baseLinkUrl
     * @return $this
     */
    public function setBaseLinkUrl($baseLinkUrl)
    {
        return $this->setData(self::KEY_BASE_LINK_URL, $baseLinkUrl);
    }

    /**
     * Get timezone of the store
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->_get(self::KEY_TIMEZONE);
    }

    /**
     * Set timezone of the store
     *
     * @param string $timezone
     * @return $this
     */
    public function setTimezone($timezone)
    {
        return $this->setData(self::KEY_TIMEZONE, $timezone);
    }

    /**
     * Get base static URL for the store
     *
     * @return string
     */
    public function getBaseStaticUrl()
    {
        return $this->_get(self::KEY_BASE_STATIC_URL);
    }

    /**
     * Set base static URL for the store
     *
     * @param string $baseStaticUrl
     * @return $this
     */
    public function setBaseStaticUrl($baseStaticUrl)
    {
        return $this->setData(self::KEY_BASE_STATIC_URL, $baseStaticUrl);
    }

    /**
     * Get base media URL for the store
     *
     * @return string
     */
    public function getBaseMediaUrl()
    {
        return $this->_get(self::KEY_BASE_MEDIA_URL);
    }

    /**
     * Set base media URL for the store
     *
     * @param string $baseMediaUrl
     * @return $this
     */
    public function setBaseMediaUrl($baseMediaUrl)
    {
        return $this->setData(self::KEY_BASE_MEDIA_URL, $baseMediaUrl);
    }

    /**
     * Get secure base URL for the store
     *
     * @return string
     */
    public function getSecureBaseUrl()
    {
        return $this->_get(self::KEY_SECURE_BASE_URL);
    }

    /**
     * Set secure base URL
     *
     * @param string $secureBaseUrl
     * @return $this
     */
    public function setSecureBaseUrl($secureBaseUrl)
    {
        return $this->setData(self::KEY_SECURE_BASE_URL, $secureBaseUrl);
    }

    /**
     * Get secure base link URL for the store
     *
     * @return string
     */
    public function getSecureBaseLinkUrl()
    {
        return $this->_get(self::KEY_SECURE_BASE_LINK_URL);
    }

    /**
     * Set secure base link URL for the store
     *
     * @param string $secureBaseLinkUrl
     * @return $this
     */
    public function setSecureBaseLinkUrl($secureBaseLinkUrl)
    {
        return $this->setData(self::KEY_SECURE_BASE_LINK_URL, $secureBaseLinkUrl);
    }

    /**
     * Get secure base static URL for the store
     *
     * @return string
     */
    public function getSecureBaseStaticUrl()
    {
        return $this->_get(self::KEY_SECURE_BASE_STATIC_URL);
    }

    /**
     * Set secure base static URL for the store
     *
     * @param string $secureBaseStaticUrl
     * @return $this
     */
    public function setSecureBaseStaticUrl($secureBaseStaticUrl)
    {
        return $this->setData(self::KEY_SECURE_BASE_STATIC_URL, $secureBaseStaticUrl);
    }

    /**
     * Get secure base media URL for the store
     *
     * @return string
     */
    public function getSecureBaseMediaUrl()
    {
        return $this->_get(self::KEY_SECURE_BASE_MEDIA_URL);
    }

    /**
     * Set secure base media URL for the store
     *
     * @param string $secureBaseMediaUrl
     * @return $this
     */
    public function setSecureBaseMediaUrl($secureBaseMediaUrl)
    {
        return $this->setData(self::KEY_SECURE_BASE_MEDIA_URL, $secureBaseMediaUrl);
    }

    /**
     * @inheritdoc
     *
     * @return \Magento\Store\Api\Data\StoreConfigExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     *
     * @param \Magento\Store\Api\Data\StoreConfigExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Store\Api\Data\StoreConfigExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
