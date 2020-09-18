<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;

/**
 * Manages locale config information.
 */
class Resolver implements ResolverInterface
{
    /**
     * Resolver default locale
     */
    const DEFAULT_LOCALE = 'en_US';

    /**
     * Default locale code
     *
     * @var string
     */
    protected $defaultLocale;

    /**
     * Scope type
     *
     * @var string
     */
    protected $scopeType;

    /**
     * Locale code
     *
     * @var string
     */
    protected $locale;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Emulated locales stack
     *
     * @var array
     */
    protected $emulatedLocales = [];

    /**
     * @var string
     */
    private $defaultLocalePath;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string $defaultLocalePath
     * @param string $scopeType
     * @param mixed $locale
     * @param DeploymentConfig|null $deploymentConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $defaultLocalePath,
        $scopeType,
        $locale = null,
        DeploymentConfig $deploymentConfig = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->defaultLocalePath = $defaultLocalePath;
        $this->scopeType = $scopeType;
        $this->deploymentConfig = $deploymentConfig ?: ObjectManager::getInstance()->get(DeploymentConfig::class);
        $this->setLocale($locale);
    }

    /**
     * @inheritdoc
     */
    public function getDefaultLocalePath()
    {
        return $this->defaultLocalePath;
    }

    /**
     * @inheritdoc
     */
    public function setDefaultLocale($locale)
    {
        $this->defaultLocale = $locale;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultLocale()
    {
        if (!$this->defaultLocale) {
            $locale = false;
            if ($this->deploymentConfig->isAvailable() && $this->deploymentConfig->isDbAvailable()) {
                $locale = $this->scopeConfig->getValue($this->getDefaultLocalePath(), $this->scopeType);
            }
            if (!$locale) {
                $locale = self::DEFAULT_LOCALE;
            }
            $this->defaultLocale = $locale;
        }
        return $this->defaultLocale;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getLocale()
    {
        if ($this->locale === null) {
            $this->setLocale();
        }
        return $this->locale;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
