<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\App\Request\Http;

class CancelTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @inheritDoc
     */
    public function setUp()
    {
        $this->resource = 'Magento_Sales::cancel';
        $this->uri = 'backend/sales/order/cancel';
        $this->httpMethod = Http::METHOD_POST;
        parent::setUp();
    }
}
