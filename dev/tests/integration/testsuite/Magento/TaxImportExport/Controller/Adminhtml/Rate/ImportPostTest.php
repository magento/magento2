<?php
/***
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
