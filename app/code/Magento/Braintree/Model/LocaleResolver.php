<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Braintree\Gateway\Config\PayPal\Config;

/**
 * Class \Magento\Braintree\Model\LocaleResolver
 *
 * @since 2.2.0
 */
class LocaleResolver implements ResolverInterface
{
    /**
     * @var ResolverInterface
     * @since 2.2.0
     */
    private $resolver;

    /**
     * @var Config
     * @since 2.2.0
     */
    private $config;

    /**
     * @param ResolverInterface $resolver
     * @param Config $config
     * @since 2.2.0
     */
    public function __construct(ResolverInterface $resolver, Config $config)
    {
        $this->resolver = $resolver;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getDefaultLocalePath()
    {
        return $this->resolver->getDefaultLocalePath();
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setDefaultLocale($locale)
    {
        return $this->resolver->setDefaultLocale($locale);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getDefaultLocale()
    {
        return $this->resolver->getDefaultLocale();
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setLocale($locale = null)
    {
        return $this->resolver->setLocale($locale);
    }

    /**
     * Gets store's locale or the `en_US` locale if store's locale does not supported by PayPal.
     *
     * @return string
     * @since 2.2.0
     */
    public function getLocale()
    {
        $locale = $this->resolver->getLocale();
        $allowedLocales = $this->config->getValue('supported_locales');

        return strpos($allowedLocales, $locale) !== false ? $locale : 'en_US';
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function emulate($scopeId)
    {
        return $this->resolver->emulate($scopeId);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function revert()
    {
        return $this->resolver->revert();
    }
}
