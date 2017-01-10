<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol;

class SaveTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{

    /**
     * Test save action
     *
     * @magentoConfigFixture               currency/options/allow USD
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @dataProvider currencySymbolDataProvider
     */
    public function testSaveAction($currencyCode, $inputCurrencySymbol, $outputCurrencySymbol)
    {
        /** @var \Magento\CurrencySymbol\Model\System\Currencysymbol $currencySymbol */
        $currencySymbol = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\CurrencySymbol\Model\System\Currencysymbol::class
        );

        $currencySymbolOriginal = $currencySymbol->getCurrencySymbol($currencyCode);

        $request = $this->getRequest();
        $request->setParam(
            'custom_currency_symbol',
            [
                $currencyCode => $inputCurrencySymbol,
            ]
        );
        $this->dispatch('backend/admin/system_currencysymbol/save');

        $this->assertRedirect();

        $this->assertEquals(
            $outputCurrencySymbol,
            $currencySymbol->getCurrencySymbol($currencyCode),
            'Currency symbol has not been saved'
        );

        //restore current symbol
        $currencySymbol->setCurrencySymbolsData([$currencyCode => $currencySymbolOriginal]);
    }

    /**
     * @return array
     */
    public function currencySymbolDataProvider()
    {
        return [
            ['USD', 'customSymbolUSD', 'customSymbolUSD'],
            ['USD', '<script>customSymbolUSD</script>', 'customSymbolUSD']
        ];
    }
}
