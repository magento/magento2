<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api\StockRepository;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Method Get is covered in CreateTest test
 * @see \Magento\InventoryApi\Test\Api\StockRepository\CreateTest::getStockDataById
 */
class GetTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/stocks';
    const SERVICE_NAME = 'inventoryApiStockRepositoryV1';
    /**#@-*/

    public function testGetNoSuchEntityException()
    {
        $notExistingId = -1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $notExistingId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $expectedMessage = 'Stock with id "%value" does not exist.';
        try {
            (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
                ? $this->_webApiCall($serviceInfo)
                : $this->_webApiCall($serviceInfo, ['stockId' => $notExistingId]);
            $this->fail('Expected throwing exception');
        } catch (\Exception $e) {
            if (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST) {
                $errorData = $this->processRestExceptionResult($e);
                self::assertEquals($expectedMessage, $errorData['message']);
                self::assertEquals($notExistingId, $errorData['parameters']['value']);
                self::assertEquals(Exception::HTTP_NOT_FOUND, $e->getCode());
            } elseif (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
                $this->assertInstanceOf('SoapFault', $e);
                $this->checkSoapFault($e, $expectedMessage, 'env:Sender', ['value' => $notExistingId]);
            } else {
                throw $e;
            }
        }
    }
}
