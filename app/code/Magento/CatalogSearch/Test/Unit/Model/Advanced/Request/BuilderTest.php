<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Advanced\Request;

use Magento\CatalogSearch\Model\Advanced\Request\Builder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\Request;
use Magento\Framework\Search\Request\Binder;
use Magento\Framework\Search\Request\Cleaner;
use Magento\Framework\Search\Request\Config;
use Magento\Framework\Search\Request\Mapper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @var Builder
     */
    private $requestBuilder;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var Mapper|MockObject
     */
    private $requestMapper;

    /**
     * @var Request|MockObject
     */
    private $request;

    /**
     * @var Binder|MockObject
     */
    private $binder;

    /**
     * @var Cleaner|MockObject
     */
    private $cleaner;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->requestMapper = $this->getMockBuilder(Mapper::class)
            ->onlyMethods(['getRootQuery', 'getBuckets'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->binder = $this->getMockBuilder(Binder::class)
            ->onlyMethods(['bind'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->cleaner = $this->getMockBuilder(Cleaner::class)
            ->onlyMethods(['clean'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestBuilder = $helper->getObject(
            Builder::class,
            [
                'config' => $this->config,
                'objectManager' => $this->objectManager,
                'binder' => $this->binder,
                'cleaner' => $this->cleaner
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreate(): void
    {
        $data = [
            'dimensions' => [
                'scope' => [
                    'name' => 'scope',
                    'value' => 'default'
                ]
            ],
            'queries' => [
                'filter_search_query' => [
                    'name' => 'filter_search_query',
                    'filterReference' => [
                        [
                            'ref' => 'boolFilter'
                        ]
                    ],
                    'type' => 'filteredQuery'
                ]
            ],
            'filters' => [
                'boolFilter' => [
                    'name' => 'boolFilter',
                    'filterReference' => [
                        [
                            'clause' => 'should',
                            'ref' => 'from_to'
                        ],
                        [
                            'clause' => 'should',
                            'ref' => 'not_array'
                        ],
                        [
                            'clause' => 'should',
                            'ref' => 'like'
                        ]
                    ],
                    'type' => 'boolFilter'
                ],
                'from_to' => [
                    'name' => 'from_to',
                    'field' => 'product_id',
                    'type' => 'rangeFilter',
                    'from' => '$from_to.from$',
                    'to' => '$from_to.to$'
                ],
                'not_array' => [
                    'name' => 'not_array',
                    'field' => 'product_id',
                    'type' => 'termFilter',
                    'value' => '$not_array$'
                ],
                'like' => [
                    'name' => 'like',
                    'field' => 'product_id',
                    'type' => 'wildcardFilter',
                    'value' => '$like$'
                ],
                'in' => [
                    'name' => 'in',
                    'field' => 'product_id',
                    'type' => 'termFilter',
                    'value' => '$in$'
                ],
                'in_set' => [
                    'name' => 'in_set',
                    'field' => 'product_id',
                    'type' => 'termFilter',
                    'value' => '$in_set$'
                ]
            ],
            'from' => '10',
            'size' => '10',
            'query' => 'one_match_filters',
            'index' => 'catalogsearch_fulltext',
            'aggregations' => []
        ];
        $requestName = 'rn';
        $bindData = [
            'dimensions' => ['scope' => 'default'],
            'placeholder' => [
                '$from_to.from$' => 10,
                '$from_to.to$' => 20,
                '$not_array$' => 130,
                '$like$' => 'search_text',
                '$in$' => 23,
                '$in_set$' => [12, 23, 34, 45]
            ],
            'requestName' => $requestName,
            'from' => 10,
            'size' => 10
        ];
        $this->requestBuilder->bindRequestValue('from_to', ['from' => 10, 'to' => 20]);
        $this->requestBuilder->bindRequestValue('not_array', 130);
        $this->requestBuilder->bindRequestValue('like', ['like' => 'search_text']);
        $this->requestBuilder->bindRequestValue('in', ['in' => 23]);
        $this->requestBuilder->bindRequestValue('in_set', ['in_set' => [12, 23, 34, 45]]);
        $this->requestBuilder->setRequestName($requestName);
        $this->requestBuilder->setSize(10);
        $this->requestBuilder->setFrom(10);
        $this->requestBuilder->bindDimension('scope', 'default');
        $this->binder->expects($this->once())
            ->method('bind')
            ->willReturnCallback(function ($data, $bindData) {
                  return $data;
            });
        $this->cleaner->expects($this->once())
            ->method('clean')
            ->willReturn($data);
        $this->requestMapper->expects($this->once())
            ->method('getRootQuery')
            ->willReturn([]);
        $this->objectManager
            ->method('create')
            ->willReturnOnConsecutiveCalls($this->requestMapper, null, $this->request);
        $this->config->expects($this->once())
            ->method('get')
            ->with($requestName)
            ->willReturn($data);
        $result = $this->requestBuilder->create();
        $this->assertInstanceOf(Request::class, $result);
    }
}
