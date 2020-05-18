<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Request;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\Request;
use Magento\Framework\Search\Request\Binder;
use Magento\Framework\Search\Request\Builder;
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

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->config = $this->getMockBuilder(Config::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->requestMapper = $this->getMockBuilder(Mapper::class)
            ->setMethods(['getRootQuery', 'getBuckets'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->binder = $this->getMockBuilder(Binder::class)
            ->setMethods(['bind'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->cleaner = $this->getMockBuilder(Cleaner::class)
            ->setMethods(['clean'])
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

    public function testCreateInvalidArgumentExceptionNotDefined()
    {
        $this->expectException('InvalidArgumentException');
        $this->requestBuilder->create();
    }

    public function testCreateInvalidArgumentException()
    {
        $this->expectException('Magento\Framework\Search\Request\NonExistingRequestNameException');
        $this->expectExceptionMessage('Request name \'rn\' doesn\'t exist.');
        $requestName = 'rn';

        $this->requestBuilder->setRequestName($requestName);
        $this->config->expects($this->once())->method('get')->with($requestName)->willReturn(null);

        $this->requestBuilder->create();
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreate()
    {
        $data = [
            'dimensions' => [
                'scope' => [
                    'name' => 'scope',
                    'value' => 'default',
                ],
            ],
            'queries' => [
                'one_match_filters' => [
                    'name' => 'one_match_filters',
                    'boost' => '2',
                    'queryReference' => [
                        [
                            'clause' => 'must',
                            'ref' => 'fulltext_search_query',
                        ],
                        [
                            'clause' => 'must',
                            'ref' => 'fulltext_search_query2',
                        ],
                    ],
                    'type' => 'boolQuery',
                ],
                'fulltext_search_query' => [
                    'name' => 'fulltext_search_query',
                    'boost' => '5',
                    'value' => '$fulltext_search_query$',
                    'match' => [
                        [
                            'field' => 'data_index',
                            'boost' => '2',
                        ],
                    ],
                    'type' => 'matchQuery',
                ],
                'fulltext_search_query2' => [
                    'name' => 'fulltext_search_query2',
                    'filterReference' => [
                        [
                            'ref' => 'pid',
                        ],
                    ],
                    'type' => 'filteredQuery',
                ],
            ],
            'filters' => [
                'pid' => [
                    'name' => 'pid',
                    'filterReference' => [
                        [
                            'clause' => 'should',
                            'ref' => 'pidm',
                        ],
                        [
                            'clause' => 'should',
                            'ref' => 'pidsh',
                        ],
                    ],
                    'type' => 'boolFilter',
                ],
                'pidm' => [
                    'name' => 'pidm',
                    'field' => 'product_id',
                    'type' => 'rangeFilter',
                    'from' => '$pidm_from$',
                    'to' => '$pidm_to$',
                ],
                'pidsh' => [
                    'name' => 'pidsh',
                    'field' => 'product_id',
                    'type' => 'termFilter',
                    'value' => '$pidsh$',
                ],
            ],
            'from' => '10',
            'size' => '10',
            'query' => 'one_match_filters',
            'index' => 'catalogsearch_fulltext',
            'aggregations' => [],
        ];
        $requestName = 'rn';
        $this->requestBuilder->bind('fulltext_search_query', 'socks');
        $this->requestBuilder->bind('pidsh', 4);
        $this->requestBuilder->bind('pidm_from', 1);
        $this->requestBuilder->bind('pidm_to', 3);
        $this->requestBuilder->setRequestName($requestName);
        $this->requestBuilder->setSize(10);
        $this->requestBuilder->setFrom(10);
        $this->requestBuilder->bindDimension('scope', 'default');
        $this->binder->expects($this->once())->method('bind')->willReturn($data);
        $this->cleaner->expects($this->once())->method('clean')->willReturn($data);
        $this->requestMapper->expects($this->once())->method('getRootQuery')->willReturn([]);
        $this->objectManager->expects($this->at(0))->method('create')->willReturn($this->requestMapper);
        $this->objectManager->expects($this->at(2))->method('create')->willReturn($this->request);
        $this->config->expects($this->once())->method('get')->with($requestName)->willReturn($data);
        $result = $this->requestBuilder->create();
        $this->assertInstanceOf(Request::class, $result);
    }
}
