<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Advanced\Request;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\Advanced\Request\Builder
     */
    private $requestBuilder;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Search\Request\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \Magento\Framework\Search\Request\Mapper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMapper;

    /**
     * @var \Magento\Framework\Search\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Search\Request\Binder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $binder;

    /**
     * @var \Magento\Framework\Search\Request\Cleaner|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cleaner;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->config = $this->getMockBuilder(\Magento\Framework\Search\Request\Config::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->requestMapper = $this->getMockBuilder(\Magento\Framework\Search\Request\Mapper::class)
            ->setMethods(['getRootQuery', 'getBuckets'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(\Magento\Framework\Search\Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->binder = $this->getMockBuilder(\Magento\Framework\Search\Request\Binder::class)
            ->setMethods(['bind'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->cleaner = $this->getMockBuilder(\Magento\Framework\Search\Request\Cleaner::class)
            ->setMethods(['clean'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestBuilder = $helper->getObject(
            \Magento\CatalogSearch\Model\Advanced\Request\Builder::class,
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
                'filter_search_query' => [
                    'name' => 'filter_search_query',
                    'filterReference' => [
                        [
                            'ref' => 'boolFilter',
                        ],
                    ],
                    'type' => 'filteredQuery',
                ],
            ],
            'filters' => [
                'boolFilter' => [
                    'name' => 'boolFilter',
                    'filterReference' => [
                        [
                            'clause' => 'should',
                            'ref' => 'from_to',
                        ],
                        [
                            'clause' => 'should',
                            'ref' => 'not_array',
                        ],
                        [
                            'clause' => 'should',
                            'ref' => 'like',
                        ],
                    ],
                    'type' => 'boolFilter',
                ],
                'from_to' => [
                    'name' => 'from_to',
                    'field' => 'product_id',
                    'type' => 'rangeFilter',
                    'from' => '$from_to.from$',
                    'to' => '$from_to.to$',
                ],
                'not_array' => [
                    'name' => 'not_array',
                    'field' => 'product_id',
                    'type' => 'termFilter',
                    'value' => '$not_array$',
                ],
                'like' => [
                    'name' => 'like',
                    'field' => 'product_id',
                    'type' => 'wildcardFilter',
                    'value' => '$like$',
                ],
                'in' => [
                    'name' => 'in',
                    'field' => 'product_id',
                    'type' => 'termFilter',
                    'value' => '$in$',
                ],
                'in_set' => [
                    'name' => 'in_set',
                    'field' => 'product_id',
                    'type' => 'termFilter',
                    'value' => '$in_set$',
                ],
            ],
            'from' => '10',
            'size' => '10',
            'query' => 'one_match_filters',
            'index' => 'catalogsearch_fulltext',
            'aggregations' => [],
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
                '$in_set$' => [12, 23, 34, 45],
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
            ->withConsecutive([$data, $bindData])
            ->willReturn($data);
        $this->cleaner->expects($this->once())
            ->method('clean')
            ->willReturn($data);
        $this->requestMapper->expects($this->once())
            ->method('getRootQuery')
            ->willReturn([]);
        $this->objectManager->expects($this->at(0))
            ->method('create')
            ->willReturn($this->requestMapper);
        $this->objectManager->expects($this->at(2))
            ->method('create')
            ->willReturn($this->request);
        $this->config->expects($this->once())
            ->method('get')
            ->with($this->equalTo($requestName))
            ->willReturn($data);
        $result = $this->requestBuilder->create();
        $this->assertInstanceOf(\Magento\Framework\Search\Request::class, $result);
    }
}
