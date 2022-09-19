<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\OpenSearch\Test\Unit\Model;

use OpenSearch\Client;
use OpenSearch\Namespaces\IndicesNamespace;

#use Magento\AdvancedSearch\Model\Client\ClientInterface as OpenSearchClient;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\AddDefaultSearchField;
use Magento\OpenSearch\Model\Adapter\DynamicTemplates\IntegerMapper;
use Magento\OpenSearch\Model\Adapter\DynamicTemplates\PositionMapper;
use Magento\OpenSearch\Model\Adapter\DynamicTemplates\PriceMapper;
use Magento\OpenSearch\Model\Adapter\DynamicTemplates\StringMapper;
use Magento\OpenSearch\Model\Adapter\DynamicTemplatesProvider;
use Magento\OpenSearch\Model\OpenSearch;
use Magento\OpenSearch\Model\SearchClient;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ElasticsearchTest to test Elasticsearch 7
 */
class OpenSearchTest extends TestCase
{
    /**
     * @var OpenSearchClient
     */
    private $model;

    /**
     * @var Client|MockObject
     */
    private $opensearchClientMock;

    /**
     * @var IndicesNamespace|MockObject
     */
    private $indicesMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManager;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->opensearchClientMock = $this->getMockBuilder(Client::class)
            ->setMethods(
                [
                    'search'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchClientMock = $this->getMockBuilder(OpenSearch::class)
            ->setMethods(
                [
                    'getOpenSearchClient',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->indicesMock = $this->getMockBuilder(IndicesNamespace::class)
            ->setMethods(
                [
                    'putMapping'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManagerHelper($this);
        $dynamicTemplatesProvider = new DynamicTemplatesProvider(
            [ new PriceMapper(), new PositionMapper(), new StringMapper(), new IntegerMapper()]
        );
        $this->model = $this->objectManager->getObject(
            OpenSearch::class,
            [
                'options' => $this->getOptions(),
                'opensearchClient' => $this->opensearchClientMock,
                'fieldsMappingPreprocessors' => [new AddDefaultSearchField()],
                'dynamicTemplatesProvider' => $dynamicTemplatesProvider,
            ]
        );
    }

    public function testPrint()
    {
        $this->assertTrue(true, "This is it.");
    }

    /**
     * Test query() method
     *
     * @return void
     */
    public function testQuery()
    {
        $query = ['test phrase query'];
        $this->opensearchClientMock->expects($this->once())
            ->method('search')
            ->with($query)
            ->willReturn([]);

        $this->searchClientMock->expects($this->once())
            ->method('getOpenSearchClient')
            ->willReturn($this->opensearchClientMock);

        $this->assertEquals([], $this->model->query($query));
    }
    /**
     * Get elasticsearch client options
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            'hostname' => 'localhost',
            'port' => '9200',
            'timeout' => 15,
            'index' => 'magento2',
            'enableAuth' => 1,
            'username' => 'user',
            'password' => 'passwd',
        ];
    }

    /**
     * Test testAddFieldsMapping() method
     */
    public function testAddFieldsMapping()
    {
        $this->indicesMock->expects($this->once())
            ->method('putMapping')
            ->with(
                [
                    'index' => 'indexName',
                    'type' => 'product',
                    'include_type_name' => true,
                    'body' => [
                        'product' => [
                            'properties' => [
                                '_search' => [
                                    'type' => 'text',
                                ],
                                'name' => [
                                    'type' => 'text',
                                ],
                            ],
                            'dynamic_templates' => [
                                [
                                    'price_mapping' => [
                                        'match' => 'price_*',
                                        'match_mapping_type' => 'string',
                                        'mapping' => [
                                            'type' => 'double',
                                            'store' => true,
                                        ],
                                    ],
                                ],
                                [
                                    'position_mapping' => [
                                        'match' => 'position_*',
                                        'match_mapping_type' => 'string',
                                        'mapping' => [
                                            'type' => 'integer',
                                            'index' => true,
                                        ],
                                    ],
                                ],
                                [
                                    'string_mapping' => [
                                        'match' => '*',
                                        'match_mapping_type' => 'string',
                                        'mapping' => [
                                            'type' => 'text',
                                            'index' => true,
                                            'copy_to' => '_search',
                                        ],
                                    ],
                                ],
                                [
                                    'integer_mapping' => [
                                        'match_mapping_type' => 'long',
                                        'mapping' => [
                                            'type' => 'integer',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );
        $this->model->addFieldsMapping(
            [
                'name' => [
                    'type' => 'text',
                ],
            ],
            'indexName',
            'product'
        );
    }
}
