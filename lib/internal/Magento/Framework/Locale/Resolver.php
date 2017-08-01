<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class \Magento\Framework\Locale\Resolver
 *
 * @since 2.0.0
 */
class Resolver implements ResolverInterface
{
    /**
     * Default locale
     */
    const DEFAULT_LOCALE = 'en_US';

    /**
     * Default locale code
     *
     * @var string
     * @since 2.0.0
     */
    protected $defaultLocale;

    /**
     * Scope type
     *
     * @var string
     * @since 2.0.0
     */
    protected $scopeType;

    /**
     * Locale code
     *
     * @var string
     * @since 2.0.0
     */
    protected $locale;

    /**
     * @var ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * Emulated locales stack
     *
     * @var array
     * @since 2.0.0
     */
    protected $emulatedLocales = [];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string $defaultLocalePath
     * @param string $scopeType
     * @param mixed $locale
     * @since 2.0.0
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $defaultLocalePath,
        $scopeType,
        $locale = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->defaultLocalePath = $defaultLocalePath;
        $this->scopeType = $scopeType;
        $this->setLocale($locale);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getDefaultLocalePath()
    {
        return $this->defaultLocalePath;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setDefaultLocale($locale)
    {
        $this->defaultLocale = $locale;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getDefaultLocale()
    {
        if (!$this->defaultLocale) {
            $locale = $this->scopeConfig->getValue($this->getDefaultLocalePath(), $this->scopeType);
            if (!$locale) {
                $locale = self::DEFAULT_LOCALE;
            }
            $this->defaultLocale = $locale;
        }
        return $this->defaultLocale;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setLocale($locale = null)
    {
        if ($locale !== null && is_string($locale)) {
            $this->locale = $locale;
        } else {
            $this->locale = $this->getDefaultLocale();
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getLocale()
    {
        if ($this->locale === null) {
            $this->setLocale();
        }
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function emulate($scopeId)
    {
        $result = null;
        if ($scopeId) {
            $this->emulatedLocales[] = $this->getLocale();
            $this->locale = $this->scopeConfig->getValue(
                $this->getDefaultLocalePath(),
                $this->scopeType,
                $scopeId
            );
            $result = $this->locale;
        } else {
            $this->emulatedLocales[] = false;
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function revert()
    {
        $result = null;
        $localeCode = array_pop($this->emulatedLocales);
        if ($localeCode) {
            $this->locale = $localeCode;
            $result = $this->locale;
        }
        return $result;
    }
}
