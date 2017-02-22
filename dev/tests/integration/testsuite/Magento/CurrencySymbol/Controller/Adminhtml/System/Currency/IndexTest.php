<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currency;

class IndexTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Test index action
     */
    public function testIndexAction()
    {
        $this->dispatch('backend/admin/system_currency/index');
        $body = $this->getResponse()->getBody();
        $this->assertContains('id="rate-form"', $body);
        $this->assertContains('save primary save-currency-rates', $body);
        $this->assertContains('data-ui-id="page-actions-toolbar-reset-button"', $body);
    }
}
