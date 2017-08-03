<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

/**
 * Class \Magento\Framework\Locale\Currency
 *
 * @since 2.0.0
 */
class Currency implements \Magento\Framework\Locale\CurrencyInterface
{
    /**
     * Default currency
     */
    const DEFAULT_CURRENCY = 'USD';

    /**#@+
     * Currency Options
     */
    const CURRENCY_OPTION_SYMBOL = 'symbol';

    const CURRENCY_OPTION_CURRENCY = 'currency';

    const CURRENCY_OPTION_NAME = 'name';

    const CURRENCY_OPTION_DISPLAY = 'display';

    /**
     * @var array
     * @since 2.0.0
     */
    protected static $_currencyCache = [];

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     * @since 2.0.0
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\CurrencyFactory
     * @since 2.0.0
     */
    protected $_currencyFactory;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param ResolverInterface $localeResolver
     * @param \Magento\Framework\CurrencyFactory $currencyFactory
     * @since 2.0.0
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
     * @inheritdoc
     * @since 2.0.0
     */
    public function getDefaultCurrency()
    {
        return self::DEFAULT_CURRENCY;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function getCurrency($currency)
    {
        \Magento\Framework\Profiler::start('locale/currency');
        if (!isset(self::$_currencyCache[$this->_localeResolver->getLocale()][$currency])) {
            $options = [];
            try {
                $currencyObject = $this->_currencyFactory->create(
                    ['options' => $currency, 'locale' => $this->_localeResolver->getLocale()]
                );
            } catch (\Exception $e) {
                $currencyObject = $this->_currencyFactory->create(
                    ['options' => $this->getDefaultCurrency(), 'locale' => $this->_localeResolver->getLocale()]
                );
                $options[self::CURRENCY_OPTION_NAME] = $currency;
                $options[self::CURRENCY_OPTION_CURRENCY] = $currency;
                $options[self::CURRENCY_OPTION_SYMBOL] = $currency;
            }

            $options = new \Magento\Framework\DataObject($options);
            $this->_eventManager->dispatch(
                'currency_display_options_forming',
                ['currency_options' => $options, 'base_code' => $currency]
            );

            $currencyObject->setFormat($options->toArray());
            self::$_currencyCache[$this->_localeResolver->getLocale()][$currency] = $currencyObject;
        }
        \Magento\Framework\Profiler::stop('locale/currency');
        return self::$_currencyCache[$this->_localeResolver->getLocale()][$currency];
    }
}
