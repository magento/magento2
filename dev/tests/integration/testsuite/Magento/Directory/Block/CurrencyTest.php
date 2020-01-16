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

    /** @var CurrencyModel */
    private $block;

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
        $this->block = $this->layout->createBlock(Currency::class);
        $this->block->setTemplate(self::CURRENCY_SWITCHER_TEMPLATE);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @magentoConfigFixture default/currency/options/allow EUR,USD
     */
    public function testCurrencySwitcher(): void
    {
        $html = trim(preg_replace('/\s+/', ' ', strip_tags($this->getBlock()->toHtml())));
        $this->assertEquals('Currency USD - US Dollar EUR - Euro', $html);
    }

    /**
     * @magentoConfigFixture default/currency/options/allow EUR,USD
     * @magentoConfigFixture fixturestore_store currency/options/allow USD,UAH
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Directory/_files/usd_euro_rate.php
     * @magentoDataFixture Magento/Directory/_files/usd_uah_rate.php
     *
     * @return void
     */
    public function testMultiStoreCurrencySwitcher(): void
    {
        $currentStore = $this->storeManager->getStore();
        $htmlFirstStore = trim(preg_replace('/\s+/', ' ', strip_tags($this->getBlock()->toHtml())));
        $this->assertEquals('Currency USD - US Dollar EUR - Euro', $htmlFirstStore);

        try {
            $this->storeManager->setCurrentStore('fixturestore');
            $htmlSecondStore = trim(preg_replace('/\s+/', ' ', strip_tags($this->getBlock(true)->toHtml())));
            $this->assertEquals('Currency USD - US Dollar UAH - Ukrainian Hryvnia', $htmlSecondStore);
        } finally {
            $this->storeManager->setCurrentStore($currentStore);
        }
    }

    /**
     * Get currency block
     *
     * @param bool $refreshBlock
     * @return Currency
     */
    private function getBlock(bool $refreshBlock = false): Currency
    {
        if ($refreshBlock) {
            $this->block = $this->layout->createBlock(Currency::class);
            $this->block->setTemplate(self::CURRENCY_SWITCHER_TEMPLATE);
        }

        return $this->block;
    }
}
