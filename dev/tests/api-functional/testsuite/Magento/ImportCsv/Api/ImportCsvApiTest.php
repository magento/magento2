<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ImportCsv\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class ImportCsvApiTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/import/csv';
    private const SERVICE_NAME = 'importCsvApiStartImportV1';
    private const SERVICE_VERSION = 'V1';

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
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Execute'
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
