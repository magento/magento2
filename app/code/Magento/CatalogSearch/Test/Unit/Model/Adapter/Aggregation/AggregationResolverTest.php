<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Adapter\Aggregation;

use Magento\Catalog\Api\AttributeSetFinderInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\CatalogSearch\Model\Adapter\Aggregation\AggregationResolver;
use Magento\CatalogSearch\Model\Adapter\Aggregation\RequestCheckerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\Config;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AggregationResolverTest extends TestCase
{
    /**
     * @var AttributeSetFinderInterface|MockObject
     */
    private $attributeSetFinder;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var Collection|MockObject
     */
    private $attributeCollection;

    /**
     * @var AggregationResolver
     */
    private $aggregationResolver;

    /**
     * @var RequestCheckerInterface|MockObject
     */
    private $aggregationChecker;

    protected function setUp(): void
    {
        $this->attributeSetFinder = $this->createMock(AttributeSetFinderInterface::class);
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->createMock(RequestInterface::class);
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeCollection = $this->getMockBuilder(
            Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->aggregationChecker = $this->createMock(RequestCheckerInterface::class);

        $this->aggregationResolver = (new ObjectManager($this))->getObject(
            AggregationResolver::class,
            [
                'attributeSetFinder' => $this->attributeSetFinder,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'config' => $this->config,
                'attributeCollection' => $this->attributeCollection,
                'aggregationChecker' => $this->aggregationChecker
            ]
        );
    }

    public function testIsNotApplicable()
    {
        $documentIds = [1];
        $this->aggregationChecker
            ->expects($this->once())
            ->method('isApplicable')
            ->with($this->request)
            ->willReturn(false);
        $this->assertEquals([], $this->aggregationResolver->resolve($this->request, $documentIds));
    }

    public function testResolve()
    {
        $documentIds = [1, 2, 3];
        $attributeSetIds = [4, 5];
        $requestName = 'request_name';
        $select =  $this->searchCriteriaBuilder = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapter = $this->searchCriteriaBuilder = $this->createMock(AdapterInterface::class);

        $this->aggregationChecker
            ->expects($this->once())
            ->method('isApplicable')
            ->with($this->request)
            ->willReturn(true);

        $this->attributeSetFinder
            ->expects($this->once())
            ->method('findAttributeSetIdsByProductIds')
            ->with($documentIds)
            ->willReturn($attributeSetIds);
        $this->attributeCollection->expects($this->once())
            ->method('setAttributeSetFilter')
            ->with($attributeSetIds)
            ->willReturnSelf();
        $this->attributeCollection->expects($this->once())
            ->method('setEntityTypeFilter')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE)
            ->willReturnSelf();
        $this->attributeCollection->expects($this->atLeastOnce())
            ->method('getSelect')
            ->willReturn($select);
        $select->expects($this->once())->method('reset')->with(Select::COLUMNS)->willReturnSelf();
        $select->expects($this->once())->method('columns')->with('attribute_code')->willReturnSelf();
        $this->attributeCollection->expects($this->once())->method('getConnection')->willReturn($adapter);
        $adapter->expects($this->once())->method('fetchCol')->with($select)->willReturn(['code_1', 'code_2']);

        $bucketFirst = $this->createMock(BucketInterface::class);
        $bucketFirst->expects($this->once())
            ->method('getField')
            ->willReturn('code_1');
        $bucketSecond = $this->createMock(BucketInterface::class);
        $bucketSecond->expects($this->once())
            ->method('getField')
            ->willReturn('some_another_code');
        $bucketThird = $this->createMock(BucketInterface::class);
        $bucketThird->expects($this->once())
            ->method('getName')
            ->willReturn('custom_not_attribute_field');

        $this->request->expects($this->once())
            ->method('getAggregation')
            ->willReturn([$bucketFirst, $bucketSecond, $bucketThird]);
        $this->request->expects($this->once())
            ->method('getName')
            ->willReturn($requestName);

        $this->config->expects($this->once())
            ->method('get')
            ->with($requestName)
            ->willReturn([
                'aggregations' => ['custom_not_attribute_field' => []],
            ]);

        $this->assertEquals(
            [$bucketFirst, $bucketThird],
            $this->aggregationResolver->resolve($this->request, $documentIds)
        );
    }
}
