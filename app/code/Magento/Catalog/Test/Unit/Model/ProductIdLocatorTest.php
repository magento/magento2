<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductIdLocator;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for ProductIdLocator class.
 */
class ProductIdLocatorTest extends TestCase
{
    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactory;

    /**
     * @var ProductIdLocator
     */
    private $model;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->setMethods(['getMetadata'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactory = $this
            ->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            ProductIdLocator::class,
            [
                'metadataPool' => $this->metadataPool,
                'collectionFactory' => $this->collectionFactory,
            ]
        );
    }

    /**
     * Test retrieve
     */
    public function testRetrieveProductIdsBySkus()
    {
        $skus = ['sku_1', 'sku_2'];
        $collection = $this->getMockBuilder(Collection::class)
            ->setMethods(
                [
                    'getItems',
                    'addFieldToFilter',
                    'setPageSize',
                    'getLastPageNumber',
                    'setCurPage',
                    'clear'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder(ProductInterface::class)
            ->setMethods(['getSku', 'getData', 'getTypeId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $metaDataInterface = $this->getMockBuilder(EntityMetadataInterface::class)
            ->setMethods(['getLinkField'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $collection->expects($this->once())->method('addFieldToFilter')
            ->with(ProductInterface::SKU, ['in' => $skus])->willReturnSelf();
        $collection->expects($this->atLeastOnce())->method('getItems')->willReturn([$product]);
        $collection->expects($this->atLeastOnce())->method('setPageSize')->willReturnSelf();
        $collection->expects($this->atLeastOnce())->method('getLastPageNumber')->willReturn(1);
        $collection->expects($this->atLeastOnce())->method('setCurPage')->with(1)->willReturnSelf();
        $collection->expects($this->atLeastOnce())->method('clear')->willReturnSelf();
        $this->metadataPool
            ->expects($this->once())
            ->method('getMetadata')
            ->with(ProductInterface::class)
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
