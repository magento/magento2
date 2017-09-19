<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Directory\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Integration test for \Magento\Directory\Model\Observer
 */
class ObserverTest extends \PHPUnit\Framework\TestCase
{
    /** @var  ObjectManagerInterface */
    protected $objectManager;

    /** @var Observer */
    protected $observer;

    /** @var \Magento\Framework\App\MutableScopeConfig */
    protected $scopeConfig;

    /** @var string */
    protected $baseCurrency = 'USD';

    /** @var string */
    protected $baseCurrencyPath = 'currency/options/base';

    /** @var string */
    protected $allowedCurrenciesPath = 'currency/options/allow';

    /** @var \Magento\Config\Model\ResourceModel\Config */
    protected $configResource;

    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->scopeConfig = $this->objectManager->create(\Magento\Framework\App\MutableScopeConfig::class);
        $this->scopeConfig->setValue(Observer::IMPORT_ENABLE, 1, ScopeInterface::SCOPE_STORE);
        $this->scopeConfig->setValue(Observer::CRON_STRING_PATH, 'cron-string-path', ScopeInterface::SCOPE_STORE);
        $this->scopeConfig->setValue(Observer::IMPORT_SERVICE, 'webservicex', ScopeInterface::SCOPE_STORE);

        $this->configResource = $this->objectManager->get(\Magento\Config\Model\ResourceModel\Config::class);
        $this->configResource->saveConfig(
            $this->baseCurrencyPath,
            $this->baseCurrency,
            ScopeInterface::SCOPE_STORE,
            0
        );

        $this->observer = $this->objectManager->create(\Magento\Directory\Model\Observer::class);
    }

    public function testScheduledUpdateCurrencyRates()
    {
        //skipping test if service is unavailable
        $url = str_replace('{{CURRENCY_FROM}}', 'USD',
            \Magento\Directory\Model\Currency\Import\Webservicex::CURRENCY_CONVERTER_URL
        );
        $url = str_replace('{{CURRENCY_TO}}', 'GBP', $url);
        try {
            file_get_contents($url);
        } catch (\PHPUnit\Framework\Exception $e) {
            $this->markTestSkipped('http://www.webservicex.net is unavailable ');
        }

        $allowedCurrencies = 'USD,GBP,EUR';
        $this->configResource->saveConfig(
            $this->allowedCurrenciesPath,
            $allowedCurrencies,
            ScopeInterface::SCOPE_STORE,
            0
        );
        $this->observer->scheduledUpdateCurrencyRates(null);
        /** @var Currency $currencyResource */
        $currencyResource = $this->objectManager
            ->create(\Magento\Directory\Model\CurrencyFactory::class)
            ->create()
            ->getResource();
        $rates = $currencyResource->getCurrencyRates($this->baseCurrency, explode(',', $allowedCurrencies));
        $this->assertNotEmpty($rates);
    }
}
