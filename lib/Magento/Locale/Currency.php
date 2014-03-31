<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Locale;

class Currency implements \Magento\Locale\CurrencyInterface
{
    /**
     * @var array
     */
    protected static $_currencyCache = array();

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param ResolverInterface $localeResolver
     * @param \Magento\CurrencyFactory $currencyFactory
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Locale\ResolverInterface $localeResolver,
        \Magento\CurrencyFactory $currencyFactory
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
        return \Magento\Locale\CurrencyInterface::DEFAULT_CURRENCY;
    }

    /**
     * Create \Zend_Currency object for current locale
     *
     * @param   string $currency
     * @return  \Magento\Currency
     */
    public function getCurrency($currency)
    {
        \Magento\Profiler::start('locale/currency');
        if (!isset(self::$_currencyCache[$this->_localeResolver->getLocaleCode()][$currency])) {
            $options = array();
            try {
                $currencyObject = $this->_currencyFactory->create(
                    array('options' => $currency, 'locale' => $this->_localeResolver->getLocale())
                );
            } catch (\Exception $e) {
                $currencyObject = $this->_currencyFactory->create(
                    array('options' => $this->getDefaultCurrency(), 'locale' => $this->_localeResolver->getLocale())
                );
                $options['name'] = $currency;
                $options['currency'] = $currency;
                $options['symbol'] = $currency;
            }

            $options = new \Magento\Object($options);
            $this->_eventManager->dispatch(
                'currency_display_options_forming',
                array('currency_options' => $options, 'base_code' => $currency)
            );

            $currencyObject->setFormat($options->toArray());
            self::$_currencyCache[$this->_localeResolver->getLocaleCode()][$currency] = $currencyObject;
        }
        \Magento\Profiler::stop('locale/currency');
        return self::$_currencyCache[$this->_localeResolver->getLocaleCode()][$currency];
    }
}
