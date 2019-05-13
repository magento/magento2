<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api\StockRepository;

use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\TestFramework\Assert\AssertArrayContains;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class CreateTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/stocks';
    const SERVICE_NAME = 'inventoryApiStockRepositoryV1';
    /**#@-*/

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var int
     */
    private $stockId;

    protected function setUp()
    {
        parent::setUp();
        $this->stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
    }

    /**
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/529092/scenarios/1820372
     */
    public function testCreate()
    {
        $expectedData = [
            StockInterface::NAME => 'stock-name',
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $stockId = $this->_webApiCall($serviceInfo, ['stock' => $expectedData]);

        self::assertNotEmpty($stockId);
        $this->stockId = $stockId;
        AssertArrayContains::assert($expectedData, $this->getStockDataById($stockId));
    }

    protected function tearDown()
    {
        if (null !== $this->stockId) {
            $this->stockRepository->deleteById($this->stockId);
        }
        parent::tearDown();
    }

    /**
     * @param int $stockId
     * @return array
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/529092/scenarios/1849390
     */
    private function getStockDataById(int $stockId): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $stockId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $response = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['stockId' => $stockId]);
        self::assertArrayHasKey(StockInterface::STOCK_ID, $response);
        return $response;
    }
}
