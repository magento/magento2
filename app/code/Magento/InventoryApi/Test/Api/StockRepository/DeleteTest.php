<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api\StockRepository;

use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Exception;

class DeleteTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/stocks';
    const SERVICE_NAME = 'inventoryApiStockRepositoryV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/529092/scenarios/1820386
     */
    public function testDeleteById()
    {
        $stockIdToDelete = 10;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $stockIdToDelete,
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];
        (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['stockId' => $stockIdToDelete]);

        $this->checkIsStockDeleted($stockIdToDelete);
    }

    /**
     * @param int $deletedStockId
     * @throws \Exception
     */
    private function checkIsStockDeleted($deletedStockId)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $deletedStockId,
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
                : $this->_webApiCall($serviceInfo, ['stockId' => $deletedStockId]);
            $this->fail('Expected throwing exception');
        } catch (\Exception $e) {
            if (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST) {
                $errorData = $this->processRestExceptionResult($e);
                self::assertEquals($expectedMessage, $errorData['message']);
                self::assertEquals($deletedStockId, $errorData['parameters']['value']);
                self::assertEquals(Exception::HTTP_NOT_FOUND, $e->getCode());
            } elseif (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
                $this->assertInstanceOf('SoapFault', $e);
                $this->checkSoapFault($e, $expectedMessage, 'env:Sender', ['value' => $deletedStockId]);
            } else {
                throw $e;
            }
        }
    }
}
