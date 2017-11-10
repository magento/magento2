<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model;

use Magento\Directory\Model\Currency as CurrencyModel;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for CurrencySystemConfig model.
 */
class CurrencySystemConfigTest extends TestCase
{
    /**
     * @var string
     */
    private $baseCurrencyPath = 'currency/options/base';

    /**
     * @var string
     */
    private $defaultCurrencyPath = 'currency/options/default';

    /**
     * @var string
     */
    private $allowedCurrenciesPath = 'currency/options/allow';

    /**
     * @var ConfigInterface
     */
    private $configResource;

    /**
     * @var CurrencyModel
     */
    private $currency;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->currency = Bootstrap::getObjectManager()->get(CurrencyModel::class);
        $this->configResource = Bootstrap::getObjectManager()->get(ConfigInterface::class);
    }

    /**
     * Test CurrencySystemConfig won't read system config, if values present in DB.
     */
    public function testGetConfigCurrenciesWithDbValues()
    {
        $this->clearCurrencyConfig();
        $allowedCurrencies = 'BDT,BNS,BTD,USD';
        $baseCurrency = 'BDT';
        $this->configResource->saveConfig(
            $this->baseCurrencyPath,
            $baseCurrency,
            ScopeInterface::SCOPE_STORE,
            0
        );
        $this->configResource->saveConfig(
            $this->defaultCurrencyPath,
            $baseCurrency,
            ScopeInterface::SCOPE_STORE,
            0
        );
        $this->configResource->saveConfig(
            $this->allowedCurrenciesPath,
            $allowedCurrencies,
            ScopeInterface::SCOPE_STORE,
            0
        );

        $expected = [
            'allowed' => explode(',', $allowedCurrencies),
            'base' => [$baseCurrency],
            'default' => [$baseCurrency],
        ];
        self::assertEquals($expected['allowed'], $this->currency->getConfigAllowCurrencies());
        self::assertEquals($expected['base'], $this->currency->getConfigBaseCurrencies());
        self::assertEquals($expected['default'], $this->currency->getConfigDefaultCurrencies());
    }

    /**
     * Test CurrencySystemConfig will read system config, if values don't present in DB.
     */
    public function testGetConfigCurrenciesWithNoDbValues()
    {
        $this->clearCurrencyConfig();
        $expected = [
            'allowed' => [0 => 'EUR',3 => 'USD'],
            'base' => ['USD'],
            'default' => ['USD'],
        ];
        self::assertEquals($expected['allowed'], $this->currency->getConfigAllowCurrencies());
        self::assertEquals($expected['base'], $this->currency->getConfigBaseCurrencies());
        self::assertEquals($expected['default'], $this->currency->getConfigDefaultCurrencies());
    }

    /**
     * Remove currency config form Db.
     *
     * @return void
     */
    private function clearCurrencyConfig()
    {
        $this->configResource->deleteConfig(
            $this->allowedCurrenciesPath,
            ScopeInterface::SCOPE_STORE,
            0
        );
        $this->configResource->deleteConfig(
            $this->baseCurrencyPath,
            ScopeInterface::SCOPE_STORE,
            0
        );
        $this->configResource->deleteConfig(
            $this->defaultCurrencyPath,
            ScopeInterface::SCOPE_STORE,
            0
        );
    }
}
