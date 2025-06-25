<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\SpecialPriceBulkResolver;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Session\SessionManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
     * @var SpecialPriceBulkResolver|MockObject
     */
    private SpecialPriceBulkResolver $specialPriceBulkResolver;

    /**
     * @var SessionManagerInterface
     */
    private SessionManagerInterface $customerSession;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->customerSession = $this->getMockBuilder(SessionManagerInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCustomerGroupId'])
            ->getMockForAbstractClass();

        $this->specialPriceBulkResolver = new SpecialPriceBulkResolver(
            $this->resource,
            $this->metadataPool,
            $this->customerSession
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

        $this->customerSession->expects($this->once())->method('getCustomerGroupId')->willReturn(1);
        $collection = $this->getMockBuilder(AbstractCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllIds', 'getIterator'])
            ->getMockForAbstractClass();
        $collection->expects($this->once())->method('getAllIds')->willReturn([1]);
        $collection->expects($this->any())->method('getIterator')
            ->willReturn(new \ArrayIterator([$product]));

        $metadata = $this->getMockForAbstractClass(EntityMetadataInterface::class);
        $metadata->expects($this->exactly(2))->method('getLinkField')->willReturn('row_id');
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($metadata);

        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->addMethods(['from', 'joinInner', 'where', 'columns', 'joinLeft'])
            ->getMockForAbstractClass();
        $connection->expects($this->once())->method('select')->willReturnSelf();
        $connection->expects($this->once())
            ->method('from')
            ->with(['e' => 'catalog_product_super_link'])
            ->willReturnSelf();
        $connection->expects($this->exactly(3))
            ->method('joinLeft')
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
        $connection->expects($this->once())->method('fetchAll')->willReturn(
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
