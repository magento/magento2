<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Pricing\Price\SpecialPriceBulkResolver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class SpecialPriceBulkResolverTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    private ResourceConnection $resource;

    /**
     * @var MetadataPool|MockObject
     */
    private MetadataPool $metadataPool;

    /**
     * @var FrontendInterface|MockObject
     */
    private FrontendInterface $cache;

    /**
     * @var SpecialPriceBulkResolver|MockObject
     */
    private SpecialPriceBulkResolver $specialPriceBulkResolver;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->cache = $this->getMockForAbstractClass(FrontendInterface::class);

        $this->specialPriceBulkResolver = new SpecialPriceBulkResolver(
            $this->resource,
            $this->metadataPool,
            $this->cache
        );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGenerateSpecialPriceMapNoCollection(): void
    {
        $this->assertEmpty($this->specialPriceBulkResolver->generateSpecialPriceMap(1, null));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGenerateSpecialPriceMapCollection(): void
    {
        $product = $this->createMock(Product::class);
        $product->expects($this->once())->method('getTypeId')->willReturn(Configurable::TYPE_CODE);
        $product->expects($this->once())->method('getEntityId')->willReturn(1);

        $collection = $this->getMockBuilder(AbstractCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllIds', 'getIterator', 'getItems'])
            ->getMockForAbstractClass();
        $collection->expects($this->once())->method('getAllIds')->willReturn([1]);
        $collection->expects($this->any())->method('getIterator')
            ->willReturn(new \ArrayIterator([$product]));
        $collection->expects($this->once())->method('getItems')->willReturn([$product]);

        $this->cache->expects($this->once())->method('load')->willReturn(null);
        $this->cache->expects($this->once())->method('save')->willReturn(true);

        $metadata = $this->getMockForAbstractClass(EntityMetadataInterface::class);
        $metadata->expects($this->exactly(2))->method('getLinkField')->willReturn('row_id');
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($metadata);

        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->addMethods(['from', 'joinInner', 'where', 'columns'])
            ->getMockForAbstractClass();
        $connection->expects($this->once())->method('select')->willReturnSelf();
        $connection->expects($this->once())
            ->method('from')
            ->with(['link' => 'catalog_product_super_link'])
            ->willReturnSelf();
        $connection->expects($this->exactly(3))
            ->method('joinInner')
            ->willReturnSelf();
        $connection->expects($this->once())
            ->method('where')
            ->with('e.entity_id IN (1)')
            ->willReturnSelf();
        $connection->expects($this->once())->method('columns')->with(
            [
                'link.product_id',
                '(price.final_price < price.price) AS hasSpecialPrice',
                'e.row_id AS identifier',
                'e.entity_id'
            ]
        )->willReturnSelf();
        $connection->expects($this->once())->method('fetchAssoc')->willReturn(
            [
                [
                    'product_id' => 2,
                    'hasSpecialPrice' => 1,
                    'identifier' => 2,
                    'entity_id' => 1
                ]
            ]
        );
        $this->resource->expects($this->once())->method('getConnection')->willReturn($connection);
        $this->resource->expects($this->exactly(4))
            ->method('getTableName')
            ->willReturnOnConsecutiveCalls(
                'catalog_product_super_link',
                'catalog_product_entity',
                'catalog_product_website',
                'catalog_product_index_price'
            );

        $this->specialPriceBulkResolver->generateSpecialPriceMap(1, $collection);
    }
}
