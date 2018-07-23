<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currency;

class FetchRatesTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Test fetch action without service
     */
    public function testFetchRatesActionWithoutService()
    {
        $request = $this->getRequest();
        $request->setParam(
            'rate_services',
            null
        );
        $this->dispatch('backend/admin/system_currency/fetchRates');

        $this->assertSessionMessages(
            $this->contains('Please specify a correct Import Service.'),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Test save action with nonexistent service
     */
    public function testFetchRatesActionWithNonexistentService()
    {
        $request = $this->getRequest();
        $request->setParam(
            'rate_services',
            'non-existent-service'
        );
        $this->dispatch('backend/admin/system_currency/fetchRates');

        $this->assertSessionMessages(
            $this->contains('We can\'t initialize the import model.'),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }
}
