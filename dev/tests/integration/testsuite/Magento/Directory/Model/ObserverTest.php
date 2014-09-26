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

namespace Magento\Directory\Model;

use Magento\Framework\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Integration test for \Magento\Directory\Model\Observer
 */
class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ObjectManager */
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

    /** @var \Magento\Core\Model\Resource\Config */
    protected $configResource;

    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->scopeConfig = $this->objectManager->create('Magento\Framework\App\MutableScopeConfig');
        $this->scopeConfig->setValue(Observer::IMPORT_ENABLE, 1, ScopeInterface::SCOPE_STORE);
        $this->scopeConfig->setValue(Observer::CRON_STRING_PATH, 'cron-string-path', ScopeInterface::SCOPE_STORE);
        $this->scopeConfig->setValue(Observer::IMPORT_SERVICE, 'webservicex', ScopeInterface::SCOPE_STORE);

        $this->configResource = $this->objectManager->get('Magento\Core\Model\Resource\Config');
        $this->configResource->saveConfig(
            $this->baseCurrencyPath,
            $this->baseCurrency,
            ScopeInterface::SCOPE_STORE,
            0
        );

        $this->observer = $this->objectManager->create('Magento\Directory\Model\Observer');
    }

    public function testScheduledUpdateCurrencyRates()
    {
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
            ->create('Magento\Directory\Model\CurrencyFactory')
            ->create()
            ->getResource();
        $rates = $currencyResource->getCurrencyRates($this->baseCurrency, explode(',', $allowedCurrencies));
        $this->assertEquals(3, count($rates));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Required parameter 'area' was not passed
     */
    public function testScheduledUpdateCurrencyRates_invalidCurrency()
    {
        $allowedCurrencies = 'USD,GBP,XXX';
        $this->configResource->saveConfig(
            $this->allowedCurrenciesPath,
            $allowedCurrencies,
            ScopeInterface::SCOPE_STORE,
            0
        );
        $this->observer->scheduledUpdateCurrencyRates(null);
    }
}