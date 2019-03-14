<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Test\Api\StockRepository;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;

class PreventAssignedToSalesChannelsStockDeletingTest extends WebapiAbstract
{
    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_with_sales_channels.php
     * @throws \Exception
     */
    public function testCouldNotDeleteException()
    {
        $stockId = 10;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/inventory/stocks/' . $stockId,
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => 'inventoryApiStockRepositoryV1',
                'operation' => 'inventoryApiStockRepositoryV1DeleteById',
            ],
        ];
        $expectedMessage = 'Stock has at least one sale channel and could not be deleted.';
        try {
            (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
                ? $this->_webApiCall($serviceInfo)
                : $this->_webApiCall($serviceInfo, ['stockId' => $stockId]);
            $this->fail('Expected throwing exception');
        } catch (\Exception $e) {
            if (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST) {
                $errorData = $this->processRestExceptionResult($e);
                self::assertEquals($expectedMessage, $errorData['message']);
                self::assertEquals(Exception::HTTP_BAD_REQUEST, $e->getCode());
            } elseif (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
                $this->assertInstanceOf('SoapFault', $e);
                $this->checkSoapFault($e, $expectedMessage, 'env:Sender');
            } else {
                throw $e;
            }
        }
    }
}
