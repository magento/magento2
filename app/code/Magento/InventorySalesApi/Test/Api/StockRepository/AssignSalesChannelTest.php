<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Test\Api\StockRepository;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\TestFramework\Assert\AssertArrayContains;
use Magento\TestFramework\TestCase\WebapiAbstract;

class AssignSalesChannelTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/stocks';
    const SERVICE_NAME = 'inventoryApiStockRepositoryV1';
    /**#@-*/

    /**
     */
    public function testDefaultWebsiteToDefaultStock()
    {
        $stockId = 1;
        $expectedData = [
            StockInterface::STOCK_ID => $stockId,
            StockInterface::NAME => 'Default Stock',
            'extension_attributes' => [
                'sales_channels' => [
                    [
                        'type' => SalesChannelInterface::TYPE_WEBSITE,
                        'code' => 'base'
                    ]
                ]
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

        $this->_webApiCall($serviceInfo, ['stock' => $expectedData]);

        AssertArrayContains::assert($expectedData, $this->getStockDataById($stockId));
    }

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     */
    public function testAssignAdditionalWebsitesToDefaultStock()
    {
        $stockId = 1;
        $expectedData = [
            StockInterface::STOCK_ID => $stockId,
            StockInterface::NAME => 'Default Stock',
            'extension_attributes' => [
                'sales_channels' => [
                    [
                        'type' => SalesChannelInterface::TYPE_WEBSITE,
                        'code' => 'eu_website'
                    ],
                    [
                        'type' => SalesChannelInterface::TYPE_WEBSITE,
                        'code' => 'us_website'
                    ]
                ]
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

        $this->_webApiCall($serviceInfo, ['stock' => $expectedData]);

        AssertArrayContains::assert($expectedData, $this->getStockDataById($stockId));
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
     * @param array $salesChannels
     * @param array $expectedErrorData
     * @dataProvider dataProviderSalesChannelsAssignment
     */
    public function testFailedValidationSalesChannelsAssignment(array $salesChannels, array $expectedErrorData)
    {
        $stockId = 1;
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
        $this->webApiCall($serviceInfo, $data, $expectedErrorData);
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
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $serviceInfo
     * @param array $data
     * @param array $expectedErrorData
     * @return void
     * @throws \Exception
     */
    private function webApiCall(array $serviceInfo, array $data, array $expectedErrorData)
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
