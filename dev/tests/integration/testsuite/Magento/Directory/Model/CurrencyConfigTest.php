<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model;

use Magento\Directory\Model\Currency as CurrencyModel;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\App\State;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for CurrencyConfig model.
 */
class CurrencyConfigTest extends TestCase
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
    private $config;

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
        $this->config = Bootstrap::getObjectManager()->get(ConfigInterface::class);
    }

    /**
     * Test get currency config for admin, crontab and storefront areas.
     *
     * @dataProvider getConfigCurrenciesDataProvider
     * @magentoDataFixture Magento/Store/_files/store.php
     * @magentoDbIsolation disabled
     * @param string $areaCode
     * @param array $expected
     * @return void
     */
    public function testGetConfigCurrencies(string $areaCode, array $expected)
    {
        /** @var State $appState */
        $appState = Bootstrap::getObjectManager()->get(State::class);
        $appState->setAreaCode($areaCode);
        $store = Bootstrap::getObjectManager()->get(Store::class);
        $store->load('test', 'code');
        $this->clearCurrencyConfig();
        $this->setStoreConfig($store->getId());
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $storeManager->setCurrentStore($store->getId());

        if (in_array($areaCode, [Area::AREA_ADMINHTML, Area::AREA_CRONTAB])) {
            self::assertEquals($expected['allowed'], $this->currency->getConfigAllowCurrencies());
            self::assertEquals($expected['base'], $this->currency->getConfigBaseCurrencies());
            self::assertEquals($expected['default'], $this->currency->getConfigDefaultCurrencies());
        } else {
            /** @var StoreManagerInterface $storeManager */
            $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
            foreach ($storeManager->getStores() as $store) {
                $storeManager->setCurrentStore($store->getId());
                self::assertEquals(
                    $expected[$store->getCode()]['allowed'],
                    $this->currency->getConfigAllowCurrencies()
                );
                self::assertEquals(
                    $expected[$store->getCode()]['base'],
                    $this->currency->getConfigBaseCurrencies()
                );
                self::assertEquals(
                    $expected[$store->getCode()]['default'],
                    $this->currency->getConfigDefaultCurrencies()
                );
            }
        }
    }

    /**
     * Provide test data for getConfigCurrencies test.
     *
     * @return array
     */
    public function getConfigCurrenciesDataProvider()
    {
        return [
            [
                'areaCode' => Area::AREA_ADMINHTML,
                'expected' => [
                    'allowed' => ['BDT', 'BNS', 'BTD', 'EUR', 'USD'],
                    'base' => ['BDT', 'USD'],
                    'default' => ['BDT', 'USD'],
                ],
            ],
            [
                'areaCode' => Area::AREA_CRONTAB,
                'expected' => [
                    'allowed' => ['BDT', 'BNS', 'BTD', 'EUR', 'USD'],
                    'base' => ['BDT', 'USD'],
                    'default' => ['BDT', 'USD'],
                ],
            ],
            [
                'areaCode' => Area::AREA_FRONTEND,
                'expected' => [
                    'default' => [
                        'allowed' => ['EUR', 'USD'],
                        'base' => ['USD'],
                        'default' => ['USD'],
                    ],
                    'test' => [
                        'allowed' => ['BDT', 'BNS', 'BTD', 'USD'],
                        'base' => ['BDT'],
                        'default' => ['BDT'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Remove currency config form Db.
     *
     * @return void
     */
    private function clearCurrencyConfig()
    {
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        foreach ($storeManager->getStores() as $store) {
            $this->config->deleteConfig(
                $this->allowedCurrenciesPath,
                'stores',
                $store->getId()
            );
            $this->config->deleteConfig(
                $this->baseCurrencyPath,
                'stores',
                $store->getId()
            );
            $this->config->deleteConfig(
                $this->defaultCurrencyPath,
                'stores',
                $store->getId()
            );
        }
    }

    /**
     * Set allowed, base and default currency config values for given store.
     *
     * @param string $storeId
     * @return void
     */
    private function setStoreConfig(string $storeId)
    {
        $allowedCurrencies = 'BDT,BNS,BTD';
        $baseCurrency = 'BDT';
        $this->config->saveConfig(
            $this->baseCurrencyPath,
            $baseCurrency,
            'stores',
            $storeId
        );
        $this->config->saveConfig(
            $this->defaultCurrencyPath,
            $baseCurrency,
            'stores',
            $storeId
        );
        $this->config->saveConfig(
            $this->allowedCurrenciesPath,
            $allowedCurrencies,
            'stores',
            $storeId
        );
        Bootstrap::getObjectManager()->get(ReinitableConfigInterface::class)->reinit();
        Bootstrap::getObjectManager()->create(StoreManagerInterface::class)->reinitStores();
    }

    protected function tearDown()
    {
        $this->clearCurrencyConfig();
    }
}
