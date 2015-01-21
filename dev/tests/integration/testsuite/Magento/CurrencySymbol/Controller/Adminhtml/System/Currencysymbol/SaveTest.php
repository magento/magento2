<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol;

class SaveTest extends \Magento\Backend\Utility\Controller
{

    /**
     * @magentoConfigFixture               currency/options/allow  EUR, USD
     * @magentoDataFixture Magento/CurrencySymbol/_files/currency_symbol.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @dataProvider currencySymbolDataProvider
     */
    public function testSaveAction($currencyCode, $inputCurrencySymbol, $outputCurrencySymbol)
    {
                $request = $this->getRequest();
                $request->setParam(
                    'custom_currency_symbol',
                    [
                        $currencyCode => $inputCurrencySymbol,
                    ]
                );
                $this->dispatch('backend/admin/system_currencysymbol/save');

                $this->assertRedirect();

                /** @var \Magento\CurrencySymbol\Model\System\Currencysymbol $symbol */
                $currencySymbol = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                    'Magento\CurrencySymbol\Model\System\Currencysymbol'
                );

                $this->assertEquals(
                    $outputCurrencySymbol,
                    $currencySymbol->getCurrencySymbol($currencyCode),
                    'Currency symbol has not been saved'
                );
    }

    /**
     * @return array
     */
    public function currencySymbolDataProvider()
    {
        return [
            ['USD', 'customSymbolUSD', 'customSymbolUSD'],
            ['EUR', '<script>customSymbolEUR</script>', 'customSymbolEUR']
        ];
    }
}
