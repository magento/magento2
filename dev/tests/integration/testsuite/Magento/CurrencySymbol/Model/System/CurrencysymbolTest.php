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

namespace Magento\CurrencySymbol\Model\System;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for Magento\CurrencySymbol\Model\System\Currencysymbol
 *
 * @magentoAppArea adminhtml
 */
class CurrencysymbolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CurrencySymbol\Model\System\Currencysymbol
     */
    protected $currencySymbolModel;

    protected function setUp()
    {
        $this->currencySymbolModel = Bootstrap::getObjectManager()->create(
            'Magento\CurrencySymbol\Model\System\Currencysymbol'
        );
    }

    protected function tearDown()
    {
        $this->currencySymbolModel = null;
        Bootstrap::getObjectManager()->get('Magento\Framework\App\Config\ReinitableConfigInterface')->reinit();
        Bootstrap::getObjectManager()->create('Magento\Framework\StoreManagerInterface')->reinitStores();
    }

    public function testGetCurrencySymbolsData()
    {
        $currencySymbolsData = $this->currencySymbolModel->getCurrencySymbolsData();
        $this->assertArrayHasKey('USD', $currencySymbolsData, 'Default currency option for USD is missing.');
        $this->assertArrayHasKey('EUR', $currencySymbolsData, 'Default currency option for EUR is missing.');
    }

    public function testSetEmptyCurrencySymbolsData()
    {
        $currencySymbolsDataBefore = $this->currencySymbolModel->getCurrencySymbolsData();

        $this->currencySymbolModel->setCurrencySymbolsData([]);

        $currencySymbolsDataAfter = $this->currencySymbolModel->getCurrencySymbolsData();

        //Make sure symbol data is unchanged
        $this->assertEquals($currencySymbolsDataBefore, $currencySymbolsDataAfter);
    }

    public function testSetCurrencySymbolsData()
    {
        $currencySymbolsData = $this->currencySymbolModel->getCurrencySymbolsData();
        $this->assertArrayHasKey('EUR', $currencySymbolsData);

        //Change currency symbol
        $currencySymbolsData = [
            'EUR' => '@'
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
     * @depends testSetCurrencySymbolsData
     */
    public function testGetCurrencySymbol()
    {
        //dependency is added for now since tear down (or app isolation) is not helping clear the configuration data
        $this->assertEquals('@', $this->currencySymbolModel->getCurrencySymbol('EUR'));
    }

    public function testGetCurrencySymbolNonExistent()
    {
        $this->assertFalse($this->currencySymbolModel->getCurrencySymbol('AUD'));
    }
}
