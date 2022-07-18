<?php

namespace Magento\ImportCsv\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class ImportCsvApiTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/import/csv';

    /**
     * Test Rest API Import
     *
     * @param array $requestData
     * @param array $expectedResponse
     * @dataProvider getRequestData
     */
    public function testImport(array $requestData, array $expectedResponse): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ]
        ];
        $requestData['source']['csvData'] = base64_encode(file_get_contents($requestData['source']['csvData']));
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @return array
     */
    public function getRequestData(): array
    {
        return [
                ['requestData' => [
                    'source' => [
                        'entity' => 'catalog_product',
                        'behavior' => 'append',
                        'validationStrategy' => 'validation-stop-on-errors',
                        'allowedErrorCount' => '10',
                        'csvData' => __DIR__ . '/_files/products.csv'
                    ]
                ],
                'expectedResponse' => [
                    0 => 'Entities Processed: 3'
                ]],
                ['requestData' => [
                    'source' => [
                        'entity' => 'customer',
                        'behavior' => 'add_update',
                        'validationStrategy' => 'validation-stop-on-errors',
                        'allowedErrorCount' => '10',
                        'csvData' => __DIR__ . '/_files/customers.csv'
                    ]
                ],
                'expectedResponse' => [
                    0 => 'Entities Processed: 3'
                ]],
                ['requestData' => [
                    'source' => [
                        'entity' => 'advanced_pricing',
                        'behavior' => 'append',
                        'validationStrategy' => 'validation-stop-on-errors',
                        'allowedErrorCount' => '10',
                        'csvData' => __DIR__ . '/_files/advanced_pricing.csv'
                    ]
                ],
                    'expectedResponse' => [
                        0 => 'Entities Processed: 1'
                ]]
        ];
    }
}
