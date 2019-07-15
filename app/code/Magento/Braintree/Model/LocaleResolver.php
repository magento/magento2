<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Braintree\Gateway\Config\PayPal\Config;

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
     * @param Config $config
     */
    public function __construct(ResolverInterface $resolver, Config $config)
    {
        $this->resolver = $resolver;
        $this->config = $config;
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
     * @see https://braintree.github.io/braintree-web/current/PayPalCheckout.html#createPayment
     */
    public function getLocale()
    {
        $locale = $this->localeMap[$this->resolver->getLocale()] ?? $this->resolver->getLocale();
        $allowedLocales = $this->config->getValue('supported_locales');

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
