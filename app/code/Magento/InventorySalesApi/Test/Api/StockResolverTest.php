<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Test\Api;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Stock resolver api-functional test.
 */
class StockResolverTest extends WebapiAbstract
{
    /**
     * Path for REST request
     */
    const API_PATH = '/V1/inventory/stock-resolver';

    /**
     * Path for SOAP request
     */
    const SERVICE_NAME = 'inventorySalesApiStockResolverV1';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelFactory;

    /**
     * Create objects.
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->stockRepository = $this->objectManager->get(StockRepositoryInterface::class);
        $this->salesChannelFactory = $this->objectManager->get(SalesChannelInterfaceFactory::class);
    }

    /**
     * Resolve stock and check data.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     */
    public function testResolveCustomStock()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::API_PATH . '/website/eu_website',
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];

        $stockData = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, [
                'type' => 'website',
                'code' => 'eu_website',
            ]);

        $this->assertEquals(10, $stockData['stock_id']);
        $this->assertEquals('EU-stock', $stockData['name']);
        $this->assertEquals(1, count($stockData['extension_attributes']['sales_channels']));
        $this->assertEquals(
            [
                'type' => 'website',
                'code' => 'eu_website'
            ],
            $stockData['extension_attributes']['sales_channels'][0]
        );
    }

    /**
     * Resolve stock and check data after sales channels was changed.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     */
    public function testResolveCustomStockAfterChangeSalesChannelsTest()
    {
        $stockId = 20;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::API_PATH . '/website/us_website',
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];

        $stockData = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, [
                'type' => 'website',
                'code' => 'us_website',
            ]);

        $this->assertEquals($stockId, $stockData['stock_id']);
        $this->assertEquals('US-stock', $stockData['name']);
        $this->assertEquals(1, count($stockData['extension_attributes']['sales_channels']));
        $this->assertEquals(
            [
                'type' => 'website',
                'code' => 'us_website'
            ],
            $stockData['extension_attributes']['sales_channels'][0]
        );

        /** @var StockInterface $stock */
        $stock = $this->stockRepository->get($stockId);
        $salesChannel = $this->salesChannelFactory->create();
        $salesChannel->setCode('global_website');
        $salesChannel->setType(SalesChannelInterface::TYPE_WEBSITE);
        $stock->getExtensionAttributes()->setSalesChannels([$salesChannel]);
        $this->stockRepository->save($stock);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::API_PATH . '/website/global_website',
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];

        $stockData = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, [
                'type' => 'website',
                'code' => 'global_website',
            ]);

        $this->assertEquals($stockId, $stockData['stock_id']);
        $this->assertEquals('US-stock', $stockData['name']);
        $this->assertEquals(1, count($stockData['extension_attributes']['sales_channels']));
        $this->assertEquals(
            [
                'type' => 'website',
                'code' => 'global_website'
            ],
            $stockData['extension_attributes']['sales_channels'][0]
        );
    }

    /**
     * Get error when try resolve stock.
     */
    public function testResolveStockStockError()
    {
        $expectedMessage = 'No linked stock found';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::API_PATH . '/website/test_stock_resolver_error',
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];

        try {
            TESTS_WEB_API_ADAPTER === self::ADAPTER_REST ? $this->_webApiCall($serviceInfo)
                : $this->_webApiCall($serviceInfo, ['type' => 'website', 'code' => 'test_stock_resolver_error']);
            $this->fail('Expected throwing exception');
        } catch (\Exception $e) {
            if (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST) {
                $this->assertEquals($expectedMessage, $this->processRestExceptionResult($e)['message']);
            } elseif (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
                $this->assertInstanceOf('SoapFault', $e);
                $this->assertEquals($expectedMessage, $e->getMessage());
            } else {
                throw $e;
            }
        }
    }
}
