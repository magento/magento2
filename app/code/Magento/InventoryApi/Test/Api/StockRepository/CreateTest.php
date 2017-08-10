<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
    const RESOURCE_PATH = '/V1/inventory/stock';
    const SERVICE_NAME = 'inventoryStockRepositoryV1';
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

    public function testCreate()
    {
        $data = [
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
        $stockId = $this->_webApiCall($serviceInfo, ['stock' => $data]);

        self::assertNotEmpty($stockId);
        $this->stockId = $stockId;
        AssertArrayContains::assert($data, $this->getStockDataById($stockId));
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
     */
    private function getStockDataById($stockId)
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
        $response = $this->_webApiCall($serviceInfo);
        self::assertArrayHasKey(StockInterface::STOCK_ID, $response);
        return $response;
    }
}
