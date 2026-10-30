<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Locale;

class Currency implements \Magento\Framework\Locale\CurrencyInterface
{
    /**
     * Currency code used when the requested currency cannot be created
     */
    public const DEFAULT_CURRENCY = 'USD';

    /**#@+
     * Currency Options
     */
    public const CURRENCY_OPTION_SYMBOL = 'symbol';

    public const CURRENCY_OPTION_CURRENCY = 'currency';

    public const CURRENCY_OPTION_NAME = 'name';

    public const CURRENCY_OPTION_DISPLAY = 'display';

    public const CURRENCY_OPTION_PRECISION = 'precision';

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
     * @inheritdoc
     */
    public function getDefaultCurrency()
    {
        return self::DEFAULT_CURRENCY;
    }

    /**
     * @inheritdoc
     */
    public function getCurrency($currency)
    {
        \Magento\Framework\Profiler::start('locale/currency');
        if (!isset(self::$_currencyCache[$this->_localeResolver->getLocale() ?? ''][$currency])) {
            $options = [];
            try {
                $locale = $this->_localeResolver->getLocale() ?? '';
                $currencyObject = $this->_currencyFactory->create(
                    ['options' => $currency, 'locale' => $locale]
                );
                $precision = $this->getPrecision($locale, $currency);
                if ($precision !== null) {
                    $options[self::CURRENCY_OPTION_PRECISION] = $precision;
                }
            } catch (\Exception $e) {
                $currencyObject = $this->_currencyFactory->create(
                    ['options' => $this->getDefaultCurrency(), 'locale' => $this->_localeResolver->getLocale() ?? '']
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
            self::$_currencyCache[$this->_localeResolver->getLocale() ?? ''][$currency] = $currencyObject;
        }
        \Magento\Framework\Profiler::stop('locale/currency');
        return self::$_currencyCache[$this->_localeResolver->getLocale() ?? ''][$currency];
    }

    /**
     * Read ICU currency precision without preventing currency fallback or observer overrides.
     *
     * @param string $locale
     * @param string $currency
     * @return int|null
     */
    private function getPrecision(string $locale, string $currency): ?int
    {
        try {
            $formatter = new \NumberFormatter($locale ?: 'en_US', \NumberFormatter::CURRENCY);
            if (!$formatter->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $currency)) {
                return null;
            }
            $precision = $formatter->getAttribute(\NumberFormatter::MAX_FRACTION_DIGITS);
            return $precision === false ? null : (int) $precision;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
