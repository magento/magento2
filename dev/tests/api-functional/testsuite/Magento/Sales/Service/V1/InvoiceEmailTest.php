<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class InvoiceEmailTest
 */
class InvoiceEmailTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';

    const SERVICE_NAME = 'salesInvoiceManagementV1';

    /**
     * @magentoApiDataFixture Magento/Sales/_files/invoice.php
     */
    public function testInvoiceEmail()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $invoiceCollection = $objectManager->get(\Magento\Sales\Model\ResourceModel\Order\Invoice\Collection::class);
        $invoice = $invoiceCollection->getFirstItem();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/invoices/' . $invoice->getId() . '/emails',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'notify',
            ],
        ];
        $requestData = ['id' => $invoice->getId()];
        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertTrue($result);
    }
}
