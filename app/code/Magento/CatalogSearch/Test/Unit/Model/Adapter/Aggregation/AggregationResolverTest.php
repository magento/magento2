<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Adapter\Aggregation;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeSearchResultsInterface;
use Magento\CatalogSearch\Model\Adapter\Aggregation\AggregationResolver;
use Magento\Catalog\Api\AttributeSetFinderInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\Config;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AggregationResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeSetFinderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeSetFinder;

    /**
     * @var ProductAttributeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productAttributeRepository;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var AggregationResolver
     */
    private $aggregationResolver;

    protected function setUp()
    {
        $this->attributeSetFinder = $this->getMock(AttributeSetFinderInterface::class);
        $this->productAttributeRepository = $this->getMock(ProductAttributeRepositoryInterface::class);
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMock(RequestInterface::class);
        $this->config = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();

        $this->aggregationResolver = (new ObjectManager($this))->getObject(
            AggregationResolver::class,
            [
                'attributeSetFinder' => $this->attributeSetFinder,
                'productAttributeRepository' => $this->productAttributeRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'config' => $this->config,
            ]
        );
    }

    public function testResolve()
    {
        $documentIds = [1, 2, 3];
        $attributeSetIds = [4, 5];
        $requestName = 'request_name';

        $this->attributeSetFinder
            ->expects($this->once())
            ->method('findAttributeSetIdsByProductIds')
            ->with($documentIds)
            ->willReturn($attributeSetIds);

        $searchCriteria = $this->getMock(SearchCriteriaInterface::class);

        $this->searchCriteriaBuilder
            ->expects($this->once())
            ->method('addFilter')
            ->with('attribute_set_id', $attributeSetIds, 'in')
            ->willReturnSelf();
        $this->searchCriteriaBuilder
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);

        $attributeFirst = $this->getMock(ProductAttributeInterface::class);
        $attributeFirst->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn('code_1');
        $attributeSecond = $this->getMock(ProductAttributeInterface::class);
        $attributeSecond->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn('code_2');

        $searchResult = $this->getMock(ProductAttributeSearchResultsInterface::class);
        $searchResult->expects($this->once())
            ->method('getItems')
            ->willReturn([$attributeFirst, $attributeSecond]);

        $this->productAttributeRepository
            ->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResult);

        $bucketFirst = $this->getMock(BucketInterface::class);
        $bucketFirst->expects($this->once())
            ->method('getField')
            ->willReturn('code_1');
        $bucketSecond = $this->getMock(BucketInterface::class);
        $bucketSecond->expects($this->once())
            ->method('getField')
            ->willReturn('some_another_code');
        $bucketThird = $this->getMock(BucketInterface::class);
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
