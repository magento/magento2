<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\OpenSearch\Test\Unit\Model;

use OpenSearch\Client;
use OpenSearch\Namespaces\IndicesNamespace;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\AddDefaultSearchField;
use Magento\OpenSearch\Model\Adapter\DynamicTemplates\IntegerMapper;
use Magento\OpenSearch\Model\Adapter\DynamicTemplates\PositionMapper;
use Magento\OpenSearch\Model\Adapter\DynamicTemplates\PriceMapper;
use Magento\OpenSearch\Model\Adapter\DynamicTemplates\StringMapper;
use Magento\OpenSearch\Model\Adapter\DynamicTemplatesProvider;
use Magento\OpenSearch\Model\OpenSearch;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class OpensearchTest to test OpensearchTest2x
 */
class OpenSearchTest extends TestCase
{
    /**
     * @var OpenSearch
     */
    private $model;

    /**
     * @var Client|MockObject
     */
    private $opensearchV2ClientMock;

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
        $this->opensearchV2ClientMock = $this->getMockBuilder(Client::class)
            ->onlyMethods(
                [
                    'indices',
                    'search'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->indicesMock = $this->getMockBuilder(IndicesNamespace::class)
            ->onlyMethods(
                [
                    'putMapping'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->opensearchV2ClientMock->expects($this->any())
            ->method('indices')
            ->willReturn($this->indicesMock);

        $this->objectManager = new ObjectManagerHelper($this);
        $dynamicTemplatesProvider = new DynamicTemplatesProvider(
            [ new PriceMapper(), new PositionMapper(), new StringMapper(), new IntegerMapper()]
        );
        $this->model = $this->objectManager->getObject(
            OpenSearch::class,
            [
                'options' => $this->getOptions(),
                'openSearchClient' => $this->opensearchV2ClientMock,
                'fieldsMappingPreprocessors' => [new AddDefaultSearchField()],
                'dynamicTemplatesProvider' => $dynamicTemplatesProvider,
            ]
        );
    }

    /**
     * Test query() method
     *
     * @return void
     */
    public function testQuery()
    {
        $query = ['test phrase query'];
        $this->opensearchV2ClientMock->expects($this->once())
            ->method('search')
            ->with($query)
            ->willReturn([]);
        $this->assertEquals([], $this->model->query($query));
    }
    /**
     * Get client options
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
                    'body' => [
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
                                        "match_pattern" => "regex",
                                        'match' => 'price_\\d+_\\d+',
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
