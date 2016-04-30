<?php
/***
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TaxImportExport\Controller\Adminhtml\Rate;

use Magento\TestFramework\TestCase\AbstractBackendController;

class ImportPostTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function setUp()
    {
        $this->resource = 'Magento_Sales::transactions_fetch';
        $this->uri = 'backend/sales/transactions/fetch';
        parent::setUp();
    }
}
