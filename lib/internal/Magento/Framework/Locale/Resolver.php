<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

class Resolver implements \Magento\Framework\Locale\ResolverInterface
{
    /**
     * Default locale code
     *
     * @var string
     */
    protected $_defaultLocale;

    /**
     * Scope type
     *
     * @var string
     */
    protected $_scopeType;

    /**
     * Locale object
     *
     * @var \Magento\Framework\LocaleInterface
     */
    protected $_locale;

    /**
     * Locale code
     *
     * @var string
     */
    protected $_localeCode;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;

    /**
     * Emulated locales stack
     *
     * @var array
     */
    protected $_emulatedLocales = [];

    /**
     * @var \Magento\Framework\LocaleFactory
     */
    protected $_localeFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\LocaleFactory $localeFactory
     * @param string $defaultLocalePath
     * @param string $scopeType
     * @param mixed $locale
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\LocaleFactory $localeFactory,
        $defaultLocalePath,
        $scopeType,
        $locale = null
    ) {
        $this->_cache = $cache;
        $this->_scopeConfig = $scopeConfig;
        $this->_localeFactory = $localeFactory;
        $this->_defaultLocalePath = $defaultLocalePath;
        $this->_scopeType = $scopeType;
        $this->setLocale($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultLocalePath()
    {
        return $this->_defaultLocalePath;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultLocale($locale)
    {
        $this->_defaultLocale = $locale;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultLocale()
    {
        if (!$this->_defaultLocale) {
            $locale = $this->_scopeConfig->getValue($this->getDefaultLocalePath(), $this->_scopeType);
            if (!$locale) {
                $locale = \Magento\Framework\Locale\ResolverInterface::DEFAULT_LOCALE;
            }
            $this->_defaultLocale = $locale;
        }
        return $this->_defaultLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale = null)
    {
        if ($locale !== null && is_string($locale)) {
            $this->_localeCode = $locale;
        } else {
            $this->_localeCode = $this->getDefaultLocale();
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        if (!$this->_locale) {
            \Zend_Locale_Data::setCache($this->_cache->getFrontend()->getLowLevelFrontend());
            $this->_locale = $this->_localeFactory->create(['locale' => $this->getLocaleCode()]);
        } elseif ($this->_locale->__toString() != $this->_localeCode) {
            $this->setLocale($this->_localeCode);
        }

        return $this->_locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocaleCode()
    {
        if ($this->_localeCode === null) {
            $this->setLocale();
        }
        return $this->_localeCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocaleCode($code)
    {
        $this->_localeCode = $code;
        $this->_locale = null;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function emulate($scopeId)
    {
        $result = null;
        if ($scopeId) {
            $this->_emulatedLocales[] = clone $this->getLocale();
            $this->_locale = $this->_localeFactory->create(
                [
                    'locale' => $this->_scopeConfig->getValue(
                        $this->getDefaultLocalePath(),
                        $this->_scopeType,
                        $scopeId
                    ),
                ]
            );
            $this->_localeCode = $this->_locale->toString();
            $result = $this->_localeCode;
        } else {
            $this->_emulatedLocales[] = false;
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function revert()
    {
        $result = null;
        $locale = array_pop($this->_emulatedLocales);
        if ($locale) {
            $this->_locale = $locale;
            $this->_localeCode = $this->_locale->toString();
            $result = $this->_localeCode;
        }
        return $result;
    }
}
