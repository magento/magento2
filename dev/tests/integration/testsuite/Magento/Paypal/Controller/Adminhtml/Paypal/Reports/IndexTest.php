<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Paypal\Controller\Adminhtml\Paypal\Reports;

/**
 * @magentoAppArea adminhtml
 */
class IndexTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    protected function setUp(): void
    {
        $this->resource = 'Magento_Paypal::paypal_settlement_reports_view';
        $this->uri = 'backend/paypal/paypal_reports/index';
        parent::setUp();
    }
}
