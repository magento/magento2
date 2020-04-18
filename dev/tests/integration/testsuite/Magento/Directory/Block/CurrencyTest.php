<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Block;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Check currency block behaviour
 *
 * @see \Magento\Directory\Block\Currency
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class CurrencyTest extends TestCase
{
    private const CURRENCY_SWITCHER_TEMPLATE = 'Magento_Directory::currency.phtml';

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var LayoutInterface */
    private $layout;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @magentoConfigFixture current_store currency/options/allow USD
     *
     * @return void
     */
    public function testDefaultCurrencySwitcher(): void
    {
        $this->assertCurrencySwitcherPerStore('');
    }

    /**
     * @magentoConfigFixture current_store currency/options/allow EUR,USD
     *
     * @return void
     */
    public function testCurrencySwitcher(): void
    {
        $this->assertCurrencySwitcherPerStore('Currency USD - US Dollar EUR - Euro');
    }

    /**
     * @magentoConfigFixture current_store currency/options/allow USD,CNY
     * @magentoConfigFixture fixturestore_store currency/options/allow USD,UAH
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Directory/_files/usd_cny_rate.php
     * @magentoDataFixture Magento/Directory/_files/usd_uah_rate.php
     *
     * @return void
     */
    public function testMultiStoreCurrencySwitcher(): void
    {
        $this->assertCurrencySwitcherPerStore('Currency USD - US Dollar CNY - Chinese Yuan');
        $this->assertCurrencySwitcherPerStore('Currency USD - US Dollar UAH - Ukrainian Hryvnia', 'fixturestore');
    }

    /**
     * Check currency switcher diplaying per stores
     *
     * @param string $expectedData
     * @param string $storeCode
     * @return void
     */
    private function assertCurrencySwitcherPerStore(
        string $expectedData,
        string $storeCode = 'default'
    ): void {
        $currentStore = $this->storeManager->getStore();
        try {
            if ($currentStore->getCode() !== $storeCode) {
                $this->storeManager->setCurrentStore($storeCode);
            }

            $actualData = trim(preg_replace('/\s+/', ' ', strip_tags($this->getBlock()->toHtml())));
            $this->assertEquals($expectedData, $actualData);
        } finally {
            if ($currentStore->getCode() !== $storeCode) {
                $this->storeManager->setCurrentStore($currentStore);
            }
        }
    }

    /**
     * Get currency block
     *
     * @return Currency
     */
    private function getBlock(): Currency
    {
        $block = $this->layout->createBlock(Currency::class);
        $block->setTemplate(self::CURRENCY_SWITCHER_TEMPLATE);

        return $block;
    }
}
