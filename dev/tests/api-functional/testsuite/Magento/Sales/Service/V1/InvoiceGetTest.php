<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config;

/**
 * Class InvoiceGetTest
 */
class InvoiceGetTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/invoice';

    const SERVICE_READ_NAME = 'salesInvoiceRepositoryV1';

    const SERVICE_VERSION = 'V1';

    /**
     * @magentoApiDataFixture Magento/Sales/_files/invoice.php
     */
    public function testInvoiceGet()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoiceCollection = $objectManager->get('Magento\Sales\Model\Resource\Order\Invoice\Collection');
        $invoice = $invoiceCollection->getFirstItem();
        $expectedInvoiceData = [
            'grand_total' => '100.0000',
            'subtotal' => '100.0000',
            'increment_id' => $invoice->getIncrementId(),
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $invoice->getId(),
                'httpMethod' => Config::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'get',
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, ['id' => $invoice->getId()]);
        foreach ($expectedInvoiceData as $field => $value) {
            $this->assertArrayHasKey($field, $result);
            $this->assertEquals($value, $result[$field]);
        }
    }
}
