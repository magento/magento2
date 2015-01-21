<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol;

use Magento\TestFramework\Helper\Bootstrap;

class IndexTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @magentoConfigFixture               currency/options/allow  EUR, USD
     * @magentoDataFixture Magento/CurrencySymbol/_files/currency_symbol.php
     * @magentoDbIsolation enabled
     *
     */
    public function testIndexAction()
    {
        $this->dispatch('backend/admin/system_currencysymbol/index');

        $body = $this->getResponse()->getBody();
        $this->assertContains('id="currency-symbols-form"', $body);
        $this->assertContains('<input id="custom_currency_symbolUSD"', $body);
        $this->assertContains('<input id="custom_currency_symbolEUR"', $body);
        $this->assertContains('save primary save-currency-symbols', $body);
    }
}
