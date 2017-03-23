<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Webapi;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Test REST schema generation mechanisms.
 */
class JsonGenerationFromDataObjectTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    /** @var string */
    protected $baseUrl = TESTS_BASE_URL;

    /** @var string */
    protected $storeCode;

    /** @var bool */
    protected $isSingleService;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    protected function setUp()
    {
        $this->_markTestAsRestOnly("JSON generation tests are intended to be executed for REST adapter only.");

        $this->storeCode = Bootstrap::getObjectManager()->get(\Magento\Store\Model\StoreManagerInterface::class)
            ->getStore()->getCode();

        $this->productMetadata =  Bootstrap::getObjectManager()->get(\Magento\Framework\App\ProductMetadataInterface::class);

        parent::setUp();
    }

    public function testMultiServiceRetrieval()
    {
        $this->isSingleService = false;

        $resourcePath = '/schema?services=testModule5AllSoapAndRestV1,testModule5AllSoapAndRestV2';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
        ];

        $schemaContent =  $this->_webApiCall($serviceInfo);
        $this->checkActualData($this->getExpectedMultiServiceData(), $schemaContent);
    }

    public function testSingleServiceRetrieval()
    {
        $this->isSingleService = false;

        $resourcePath = '/schema?services=testModule5AllSoapAndRestV2';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
        ];

        $schemaContent =  $this->_webApiCall($serviceInfo);

        $this->checkActualData($this->getExpectedSingleServiceData(), $schemaContent);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Request does not match any route.
     */
    public function testInvalidRestUrlNoServices()
    {
        $resourcePath = '';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
        ];

        $this->_webApiCall($serviceInfo);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Incorrect format of request URI or Requested services are missing.
     */
    public function testInvalidRestUrlInvalidServiceName()
    {
        $this->isSingleService = false;

        $resourcePath = '/schema?services=invalidServiceName';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
        ];

        $this->_webApiCall($serviceInfo);
    }

    private function assertRecursiveArray($expected, $actual, $checkVal)
    {
        ksort($expected);
        ksort($actual);
        foreach ($expected as $expKey => $expVal) {
            $this->assertArrayHasKey($expKey, $actual, 'Schema does not contain \'' . $expKey . '\' section.');
            if (is_array($expVal)) {
                $this->assertTrue(is_array($actual[$expKey]));
                $this->assertRecursiveArray($expVal, $actual[$expKey], $checkVal);
            } elseif ($checkVal) {
                $this->assertEquals($expVal, $actual[$expKey], '\'' . $expKey . '\' section content is invalid.');
            }
        }
    }

    public function checkActualData($expected, $actual)
    {
        $this->assertRecursiveArray($expected, $actual, true);
    }

    public function getExpectedCommonData()
    {
        $versionParts = explode('.', $this->productMetadata->getVersion());
        if (!isset($versionParts[0]) || !isset($versionParts[1])) {
            return []; // Major and minor version are not set - return empty response
        }
        $majorMinorVersion = $versionParts[0] . '.' . $versionParts[1];
        $url = str_replace('://', '', strstr($this->baseUrl, '://'));
        $host = strpos($url, '/') ? strstr($url, '/', true) : $url;
        $basePath = strstr(rtrim($url, '/'), '/');
        $basePath = $basePath ? trim($basePath, '/') . '/' : '';
        $basePath = '/' . $basePath . 'rest/' . $this->storeCode;
        return [
            'swagger' => '2.0',
            'info' => [
                'version' => $majorMinorVersion,
                'title' => $this->productMetadata->getName() . ' ' .$this->productMetadata->getEdition(),
            ],
            'host' => $host,
            'basePath' => $basePath,
        ];
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getExpectedMultiServiceData()
    {
        $expected = [
            'tags' => [
                [
                  'name' => 'testModule5AllSoapAndRestV1',
                  'description' => 'Both SOAP and REST Version ONE',
                ],
                [
                  'name' => 'testModule5AllSoapAndRestV2',
                  'description' => 'Both SOAP and REST Version TWO',
                ],
            ],
            'paths' => [
                '/V1/TestModule5/{parentId}/nestedResource/{entityId}' =>    [
                    'put' => [
                        'tags' => [
                            'testModule5AllSoapAndRestV1',
                        ],
                        'description' => 'Update existing item.',
                        'operationId' => 'testModule5AllSoapAndRestV1NestedUpdatePut',
                        'parameters' => [
                            [
                                'name' => 'parentId',
                                'in' => 'path',
                                'type' => 'string',
                                'required' => true
                            ],
                            [
                                'name' => 'entityId',
                                'in' => 'path',
                                'type' => 'string',
                                'required' => true
                            ],
                            [
                                'name' => '$body',
                                'in' => 'body',
                                'schema' => [
                                    'required' => [
                                        'entityItem',
                                    ],
                                    'properties' => [
                                        'entityItem' => [
                                            '$ref' => '#/definitions/test-module5-v1-entity-all-soap-and-rest',
                                        ],
                                    ],
                                    'type' => 'object'
                                ],
                            ]
                        ],
                        'responses' => [
                            200 => [
                                'description' => '200 Success.',
                                'schema' => [
                                    '$ref' => '#/definitions/test-module5-v1-entity-all-soap-and-rest',
                                ],
                            ],
                            401 => [
                                'description' => '401 Unauthorized',
                                'schema' => [
                                    '$ref' => '#/definitions/error-response',
                                ],
                            ],
                            'default' => [
                                'description' => 'Unexpected error',
                                'schema' => [
                                    '$ref' => '#/definitions/error-response',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'definitions' => [
                'framework-attribute-interface' => [
                    'type' => 'object',
                    'description' => 'Interface for custom attribute value.',
                    'properties' => [
                        'attribute_code' => [
                            'type' => 'string',
                            'description' => 'Attribute code',
                        ],
                        'value' => [
                            'type' => 'string',
                            'description' => 'Attribute value',
                        ],
                    ],
                    'required' => [
                        'attribute_code',
                        'value',
                    ],
                ],
                'test-module5-v1-entity-all-soap-and-rest' => [
                    'type' => 'object',
                    'description' => 'Some Data Object short description. Data Object long multi line description.',
                    'properties' => [
                        'entity_id' => [
                            'type' => 'integer',
                                'description' => 'Item ID',
                            ],
                            'name' => [
                                'type' => 'string',
                                'description' => 'Item name',
                            ],
                            'enabled' => [
                                'type' => 'boolean',
                                'description' => 'If entity is enabled',
                            ],
                            'orders' => [
                                'type' => 'boolean',
                                'description' => 'If current entity has a property defined',
                            ],
                            'custom_attributes' =>        [
                            'type' => 'array',
                            'description' => 'Custom attributes values.',
                            'items' => [
                                '$ref' => '#/definitions/framework-attribute-interface',
                            ],
                        ],
                    ],
                    'required' => [
                        'entity_id',
                        'enabled',
                        'orders',
                    ],
                ],
            ],
        ];
        return array_merge_recursive($expected, $this->getExpectedCommonData());
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getExpectedSingleServiceData()
    {
        $expected = [
            'tags' => [
                [
                  'name' => 'testModule5AllSoapAndRestV2',
                  'description' => 'Both SOAP and REST Version TWO',
                ],
            ],
            'paths' => [
                '/V2/TestModule5/{id}' => [
                    'delete' => [
                        'tags' => [
                            'testModule5AllSoapAndRestV2',
                        ],
                        'description' => 'Delete existing item.',
                        'operationId' => 'testModule5AllSoapAndRestV2DeleteDelete',
                        'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'type' => 'string',
                                'required' => true
                            ],
                        ],
                        'responses' => [
                            200 => [
                                'description' => '200 Success.',
                                'schema' => [
                                    '$ref' => '#/definitions/test-module5-v2-entity-all-soap-and-rest',
                                ],
                            ],
                            401 => [
                                'description' => '401 Unauthorized',
                                'schema' => [
                                    '$ref' => '#/definitions/error-response',
                                ],
                            ],
                            'default' => [
                                'description' => 'Unexpected error',
                                'schema' => [
                                    '$ref' => '#/definitions/error-response',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'definitions' => [
                'test-module5-v2-entity-all-soap-and-rest' => [
                    'type' => 'object',
                    'description' => 'Some Data Object short description. Data Object long multi line description.',
                    'properties' => [
                        'price' => [
                            'type' => 'integer',
                        ],
                    ],
                    'required' => [
                        'price',
                    ],
                ],
            ],
        ];
        return array_merge($expected, $this->getExpectedCommonData());
    }
}
