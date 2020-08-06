<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\Express;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Paypal\Model\Config;

/**
 * Resolves locale for PayPal Express.
 */
class LocaleResolver implements ResolverInterface
{
    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var Config
     */
    private $config;

    /**
     * Mapping Magento locales on PayPal locales.
     *
     * @var array
     */
    private $localeMap = [
        'zh_Hans_CN' => 'zh_CN',
        'zh_Hant_HK' => 'zh_HK',
        'zh_Hant_TW' => 'zh_TW'
    ];

    /**
     * @param ResolverInterface $resolver
     * @param ConfigFactory $configFactory
     */
    public function __construct(ResolverInterface $resolver, ConfigFactory $configFactory)
    {
        $this->resolver = $resolver;
        $this->config = $configFactory->create();
        $this->config->setMethod(Config::METHOD_EXPRESS);
    }

    /**
     * @inheritdoc
     */
    public function getDefaultLocalePath()
    {
        return $this->resolver->getDefaultLocalePath();
    }

    /**
     * @inheritdoc
     */
    public function setDefaultLocale($locale)
    {
        return $this->resolver->setDefaultLocale($locale);
    }

    /**
     * @inheritdoc
     */
    public function getDefaultLocale()
    {
        return $this->resolver->getDefaultLocale();
    }

    /**
     * @inheritdoc
     */
    public function setLocale($locale = null)
    {
        return $this->resolver->setLocale($locale);
    }

    /**
     * Gets store's locale or the `en_US` locale if store's locale does not supported by PayPal.
     *
     * @return string
     * @see https://developer.paypal.com/docs/api/reference/locale-codes/#supported-locale-codes
     */
    public function getLocale(): string
    {
        $locale = $this->localeMap[$this->resolver->getLocale()] ?? $this->resolver->getLocale();
        $allowedLocales =(bool)(int) $this->config->getValue('in_context')
            ? $this->config->getValue('smart_buttons_supported_locales')
            : $this->config->getValue('supported_locales');

        return strpos($allowedLocales, $locale) !== false ? $locale : 'en_US';
    }

    /**
     * @inheritdoc
     */
    public function emulate($scopeId)
    {
        return $this->resolver->emulate($scopeId);
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
        return $this->resolver->revert();
    }
}
