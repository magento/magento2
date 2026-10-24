<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\TaxImportExport\Controller\Adminhtml\Rate;

use Magento\TestFramework\TestCase\AbstractBackendController;

class ImportExportTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    protected function setUp(): void
    {
        $this->resource = 'Magento_Sales::transactions_fetch';
        $this->uri = 'backend/sales/transactions/fetch';
        parent::setUp();
    }
}
