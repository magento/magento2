<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Test\Api\StockRepository;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

class SalesChannelManagementTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/stocks';
    const SERVICE_NAME = 'inventoryApiStockRepositoryV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     */
    public function testCreateStockWithSalesChannels()
    {
        $stockId = 10;
        $salesChannels = [
            [
                SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                SalesChannelInterface::CODE => 'eu_website',
            ],
            [
                SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                SalesChannelInterface::CODE => 'us_website',
            ],
        ];
        $stockData = [
            StockInterface::NAME => 'stock-name',
            ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY => [
                'sales_channels' => $salesChannels,
            ],
        ];
        $this->saveStock($stockId, $stockData);
        $actualStockData = $this->getStockDataById($stockId);

        self::assertArrayHasKey('sales_channels', $stockData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);
        self::assertEquals(
            $salesChannels,
            $actualStockData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['sales_channels']
        );
    }

    /**
     * The test check that the sales channels (unassigned from the sales
     * channel) will be automatically assigned to the Default Stock
     *
     * @param array $salesChannels new sales channels
     * @param array $beforeSave sales channels in default stock before unassign
     * @param array $afterSave sales channels in default stock after unassign
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_with_sales_channels.php
     *
     * @dataProvider deleteSalesChannelDataProvider
     */
    public function testValidateDeleteSalesChannelFromStock(array $salesChannels, array $beforeSave, array $afterSave)
    {
        $defaultStockData = $this->getStockDataById(1);
        $this->assertEquals($beforeSave, $defaultStockData['extension_attributes']['sales_channels']);
        $stockId = 10;
        $data = [
            StockInterface::STOCK_ID => $stockId,
            StockInterface::NAME => 'stock_with_channels_name',
            'extension_attributes' => [
                'sales_channels' => $salesChannels
            ]
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $stockId,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $this->_webApiCall($serviceInfo, ['stock' => $data]);
        $defaultStockData = $this->getStockDataById(1);
        $this->assertEquals($afterSave, $defaultStockData['extension_attributes']['sales_channels']);
        $additionalStockData = $this->getStockDataById($stockId);
        $this->assertEquals($salesChannels, $additionalStockData['extension_attributes']['sales_channels']);
    }

    /**
     * @return array
     */
    public function deleteSalesChannelDataProvider(): array
    {
        return [
            'one_channel_delete' . SalesChannelInterface::TYPE => [
                [
                    [

                        SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                        SalesChannelInterface::CODE => 'us_website',

                    ],
                ],
                [
                    [
                        SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                        SalesChannelInterface::CODE => 'base',
                    ],
                    [
                        SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                        SalesChannelInterface::CODE => 'global_website',
                    ],
                ],
                [
                    [
                        SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                        SalesChannelInterface::CODE => 'base',
                    ],
                    [
                        SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                        SalesChannelInterface::CODE => 'eu_website',
                    ],
                    [
                        SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                        SalesChannelInterface::CODE => 'global_website',
                    ],
                ],
            ],
        ];
    }
    
    /**
     * @param array $salesChannels
     * @param array $expectedErrorData
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock.php
     *
     * @dataProvider dataProviderSalesChannelsAssignment
     */
    public function testFailedValidationSalesChannelsAssignment(array $salesChannels, array $expectedErrorData)
    {
        $stockId = 10;
        $data = [
            StockInterface::STOCK_ID => $stockId,
            StockInterface::NAME => 'Default Stock',
            'extension_attributes' => [
                'sales_channels' => $salesChannels
            ]
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $stockId,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $this->webApiCallWithError($serviceInfo, $data, $expectedErrorData);
    }

    /**
     * @return array
     */
    public function dataProviderSalesChannelsAssignment(): array
    {
        return [
            'not_given_' . SalesChannelInterface::TYPE => [
                [
                    [
                        SalesChannelInterface::CODE => 'base'
                    ],
                ],
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SalesChannelInterface::TYPE,
                            ],
                        ],
                    ],
                ],
            ],
            'empty_' . SalesChannelInterface::TYPE => [
                [
                    [
                        SalesChannelInterface::TYPE => '',
                        SalesChannelInterface::CODE => 'base'
                    ],
                ],
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SalesChannelInterface::TYPE,
                            ],
                        ],
                    ],
                ],
            ],
            'not_given_' . SalesChannelInterface::CODE => [
                [
                    [
                        SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                        SalesChannelInterface::CODE => 'base'
                    ],
                    [
                        SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                    ],
                ],
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SalesChannelInterface::CODE,
                            ],
                        ],
                        [
                            'message' => 'The website with code "%code" does not exist.',
                            'parameters' => [
                                'code' => '',
                            ],
                        ],
                    ],
                ],
            ],
            'empty_' . SalesChannelInterface::CODE => [
                [
                    [
                        SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                        SalesChannelInterface::CODE => ''
                    ],
                ],
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SalesChannelInterface::CODE,
                            ],
                        ],
                        [
                            'message' => 'The website with code "%code" does not exist.',
                            'parameters' => [
                                'code' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $stockId
     * @param array $data
     * @return void
     */
    private function saveStock(int $stockId, array $data)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $stockId,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST) {
            $this->_webApiCall($serviceInfo, ['stock' => $data]);
        } else {
            $requestData = $data;
            $requestData['stockId'] = $stockId;
            $this->_webApiCall($serviceInfo, ['stock' => $requestData]);
        }
    }

    /**
     * @param int $stockId
     * @return array
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

    /**
     * @param array $serviceInfo
     * @param array $data
     * @param array $expectedErrorData
     * @return void
     * @throws \Exception
     */
    private function webApiCallWithError(array $serviceInfo, array $data, array $expectedErrorData)
    {
        try {
            $this->_webApiCall($serviceInfo, ['stock' => $data]);
            $this->fail('Expected throwing exception');
        } catch (\Exception $e) {
            if (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST) {
                self::assertEquals($expectedErrorData, $this->processRestExceptionResult($e));
                self::assertEquals(Exception::HTTP_BAD_REQUEST, $e->getCode());
            } elseif (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
                $this->assertInstanceOf('SoapFault', $e);
                $expectedWrappedErrors = [];
                foreach ($expectedErrorData['errors'] as $error) {
                    // @see \Magento\TestFramework\TestCase\WebapiAbstract::getActualWrappedErrors()
                    $expectedWrappedErrors[] = [
                        'message' => $error['message'],
                        'params' => $error['parameters'],
                    ];
                }
                $this->checkSoapFault($e, $expectedErrorData['message'], 'env:Sender', [], $expectedWrappedErrors);
            } else {
                throw $e;
            }
        }
    }
}
