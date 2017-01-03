<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model;

/**
 * Class ProductIdLocatorTest.
 */
class ProductIdLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Model\ProductIdLocator
     */
    private $model;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->searchCriteriaBuilder = $this->getMock(
            \Magento\Framework\Api\SearchCriteriaBuilder::class,
            ['addFilters', 'create'],
            [],
            '',
            false
        );
        $this->filterBuilder = $this->getMock(
            \Magento\Framework\Api\FilterBuilder::class,
            ['setField', 'setConditionType', 'setValue', 'create'],
            [],
            '',
            false
        );
        $this->metadataPool = $this->getMock(
            \Magento\Framework\EntityManager\MetadataPool::class,
            ['getMetadata'],
            [],
            '',
            false
        );
        $this->productRepository = $this->getMockForAbstractClass(
            \Magento\Catalog\Api\ProductRepositoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getList']
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\ProductIdLocator::class,
            [
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'filterBuilder' => $this->filterBuilder,
                'metadataPool' => $this->metadataPool,
                'productRepository' => $this->productRepository,
            ]
        );
    }

    /**
     * Test retrieve
     */
    public function testRetrieveProductIdsBySkus()
    {
        $skus = ['sku_1', 'sku_2'];
        $searchCriteria = $this->getMock(
            \Magento\Framework\Api\SearchCriteria::class,
            [],
            [],
            '',
            false
        );
        $searchResults = $this->getMockForAbstractClass(
            \Magento\Catalog\Api\Data\ProductSearchResultsInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getItems']
        );
        $product = $this->getMockForAbstractClass(
            \Magento\Catalog\Api\Data\ProductInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getSku', 'getData', 'getTypeId']
        );
        $metaDataInterface = $this->getMockForAbstractClass(
            \Magento\Framework\EntityManager\EntityMetadataInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getLinkField']
        );
        $this->searchCriteriaBuilder->expects($this->once())->method('addFilters')->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('setField')->with('sku')->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('setConditionType')->with('in')->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('setValue')->with(['sku_1', 'sku_2'])->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('create')->willReturnSelf();
        $this->searchCriteriaBuilder
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $this->productRepository
            ->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResults);
        $searchResults->expects($this->once())->method('getItems')->willReturn([$product]);
        $this->metadataPool
            ->expects($this->once())
            ->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($metaDataInterface);
        $metaDataInterface->expects($this->once())->method('getLinkField')->willReturn('entity_id');
        $product->expects($this->once())->method('getSku')->willReturn('sku_1');
        $product->expects($this->once())->method('getData')->with('entity_id')->willReturn(1);
        $product->expects($this->once())->method('getTypeId')->willReturn('simple');
        $this->assertEquals(
            ['sku_1' => [1 => 'simple']],
            $this->model->retrieveProductIdsBySkus($skus)
        );
    }
}
