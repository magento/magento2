<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

class Currency implements \Magento\Framework\Locale\CurrencyInterface
{
    /**
     * @var array
     */
    protected static $_currencyCache = [];

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param ResolverInterface $localeResolver
     * @param \Magento\Framework\CurrencyFactory $currencyFactory
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\CurrencyFactory $currencyFactory
    ) {
        $this->_eventManager = $eventManager;
        $this->_localeResolver = $localeResolver;
        $this->_currencyFactory = $currencyFactory;
    }

    /**
     * Retrieve currency code
     *
     * @return string
     */
    public function getDefaultCurrency()
    {
        return \Magento\Framework\Locale\CurrencyInterface::DEFAULT_CURRENCY;
    }

    /**
     * Create \Zend_Currency object for current locale
     *
     * @param   string $currency
     * @return  \Magento\Framework\Currency
     */
    public function getCurrency($currency)
    {
        \Magento\Framework\Profiler::start('locale/currency');
        if (!isset(self::$_currencyCache[$this->_localeResolver->getLocaleCode()][$currency])) {
            $options = [];
            try {
                $currencyObject = $this->_currencyFactory->create(
                    ['options' => $currency, 'locale' => $this->_localeResolver->getLocale()]
                );
            } catch (\Exception $e) {
                $currencyObject = $this->_currencyFactory->create(
                    ['options' => $this->getDefaultCurrency(), 'locale' => $this->_localeResolver->getLocale()]
                );
                $options['name'] = $currency;
                $options['currency'] = $currency;
                $options['symbol'] = $currency;
            }

            $options = new \Magento\Framework\Object($options);
            $this->_eventManager->dispatch(
                'currency_display_options_forming',
                ['currency_options' => $options, 'base_code' => $currency]
            );

            $currencyObject->setFormat($options->toArray());
            self::$_currencyCache[$this->_localeResolver->getLocaleCode()][$currency] = $currencyObject;
        }
        \Magento\Framework\Profiler::stop('locale/currency');
        return self::$_currencyCache[$this->_localeResolver->getLocaleCode()][$currency];
    }
}
