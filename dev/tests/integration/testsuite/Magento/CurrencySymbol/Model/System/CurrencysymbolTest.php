<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Model\System;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for Magento\CurrencySymbol\Model\System\Currencysymbol
 *
 * @magentoAppArea adminhtml
 */
class CurrencysymbolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CurrencySymbol\Model\System\Currencysymbol
     */
    protected $currencySymbolModel;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->currencySymbolModel = Bootstrap::getObjectManager()->create(
            \Magento\CurrencySymbol\Model\System\Currencysymbol::class
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->currencySymbolModel = null;
        Bootstrap::getObjectManager()->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class)->reinit();
        Bootstrap::getObjectManager()->create(\Magento\Store\Model\StoreManagerInterface::class)->reinitStores();
    }

    /**
     * Test that getCurrencySymbolsData method returns valid data
     *
     * @return void
     */
    public function testGetCurrencySymbolsData()
    {
        $currencySymbolsData = $this->currencySymbolModel->getCurrencySymbolsData();
        $this->assertArrayHasKey('USD', $currencySymbolsData, 'Default currency option for USD is missing.');
        $this->assertArrayHasKey('EUR', $currencySymbolsData, 'Default currency option for EUR is missing.');
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSetEmptyCurrencySymbolsData()
    {
        $currencySymbolsDataBefore = $this->currencySymbolModel->getCurrencySymbolsData();

        $this->currencySymbolModel->setCurrencySymbolsData([]);

        $currencySymbolsDataAfter = $this->currencySymbolModel->getCurrencySymbolsData();

        //Make sure symbol data is unchanged
        $this->assertEquals($currencySymbolsDataBefore, $currencySymbolsDataAfter);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSetCurrencySymbolsData()
    {
        $currencySymbolsData = $this->currencySymbolModel->getCurrencySymbolsData();
        $this->assertArrayHasKey('EUR', $currencySymbolsData);

        //Change currency symbol
        $currencySymbolsData = [
            'EUR' => '@',
        ];
        $this->currencySymbolModel->setCurrencySymbolsData($currencySymbolsData);

        //Verify if the new symbol is set
        $this->assertEquals(
            '@',
            $this->currencySymbolModel->getCurrencySymbolsData()['EUR']['displaySymbol'],
            'Symbol not set correctly.'
        );

        $this->assertEquals('@', $this->currencySymbolModel->getCurrencySymbol('EUR'), 'Symbol not set correctly.');
    }

    /**
     * Test that method returns valid data
     *
     * @return void
     */
    public function testGetCurrencySymbolNonExistent()
    {
        $this->assertFalse($this->currencySymbolModel->getCurrencySymbol('AUD'));
    }

    /**
     * Test that default symbol can be set to use explicitly in the system
     *
     * @return void
     */
    public function testSetCurrencySymbolLikeParent()
    {
        $currencySymbolsData = ['USD' => '$'];
        $this->currencySymbolModel->setCurrencySymbolsData($currencySymbolsData);

        //Verify if the new symbol is set
        $this->assertEquals(
            '$',
            $this->currencySymbolModel->getCurrencySymbolsData()['USD']['displaySymbol'],
            'Symbol was not correctly set.'
        );

        $this->assertEquals(
            false,
            $this->currencySymbolModel->getCurrencySymbolsData()['USD']['inherited'],
            'Symbol\'s inheritance was not correctly set.'
        );
    }
}
