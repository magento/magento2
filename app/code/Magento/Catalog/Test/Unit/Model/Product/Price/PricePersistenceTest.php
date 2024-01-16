<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Price;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product\Price\PricePersistence;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PricePersistenceTest extends TestCase
{
    /**
     * @var Attribute|MockObject
     */
    private $attributeResource;

    /**
     * @var ProductAttributeRepositoryInterface|MockObject
     */
    private $attributeRepository;

    /**
     * @var ProductAttributeInterface|MockObject
     */
    private $productAttribute;

    /**
     * @var ProductIdLocatorInterface|MockObject
     */
    private $productIdLocator;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    /**
     * @var PricePersistence
     */
    private $model;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->attributeResource = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeRepository = $this->getMockBuilder(
            ProductAttributeRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productIdLocator = $this->getMockBuilder(ProductIdLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLinkField', 'getMetadata'])
            ->getMock();
        $this->connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productAttribute = $this->getMockBuilder(ProductAttributeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            PricePersistence::class,
            [
                'attributeResource' => $this->attributeResource,
                'attributeRepository' => $this->attributeRepository,
                'productIdLocator' => $this->productIdLocator,
                'metadataPool' => $this->metadataPool,
            ]
        );
    }

    /**
     * Test get method.
     *
     * @return void
     */
    public function testGet()
    {
        $attributeId = 5;
        $skus = ['sku_1', 'sku_2'];
        $idsBySku = [
            'sku_1' => [
                1 => Type::TYPE_SIMPLE
            ],
            'sku_2' => [
                2 => Type::TYPE_VIRTUAL
            ]
        ];
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productIdLocator
            ->expects($this->once())
            ->method('retrieveProductIdsBySkus')->with($skus)
            ->willReturn($idsBySku);
        $this->attributeResource->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->connection);
        $this->connection->expects($this->once())->method('select')->willReturn($select);
        $this->attributeResource
            ->expects($this->once())
            ->method('getTable')
            ->with('catalog_product_entity_decimal')
            ->willReturn('catalog_product_entity_decimal');
        $select->expects($this->once())->method('from')->with('catalog_product_entity_decimal')->willReturnSelf();
        $this->attributeRepository->expects($this->once())->method('get')->willReturn($this->productAttribute);
        $this->productAttribute->expects($this->once())->method('getAttributeId')->willReturn($attributeId);
        $select
            ->expects($this->atLeastOnce())
            ->method('where')
            ->withConsecutive(['row_id IN (?)', [1, 2]], ['attribute_id = ?', $attributeId])
            ->willReturnSelf();
        $this->metadataPool->expects($this->atLeastOnce())->method('getMetadata')->willReturnSelf();
        $this->metadataPool->expects($this->atLeastOnce())->method('getLinkField')->willReturn('row_id');
        $this->model->get($skus);
    }

    /**
     * Test update method.
     *
     * @return void
     */
    public function testUpdate()
    {
        $attributeId = 5;
        $prices = [
            [
                'store_id' => 1,
                'row_id' => 1,
                'value' => 15
            ]
        ];
        $this->attributeRepository->expects($this->once())->method('get')->willReturn($this->productAttribute);
        $this->productAttribute->expects($this->once())->method('getAttributeId')->willReturn($attributeId);
        $this->attributeResource->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->connection);
        $this->connection->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $this->attributeResource
            ->expects($this->once())
            ->method('getTable')
            ->with('catalog_product_entity_decimal')
            ->willReturn('catalog_product_entity_decimal');
        $this->connection
            ->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                'catalog_product_entity_decimal',
                [
                    [
                        'store_id' => 1,
                        'row_id' => 1,
                        'value' => 15,
                        'attribute_id' => 5,
                    ]
                ],
                ['value']
            )
            ->willReturnSelf();
        $this->connection->expects($this->once())->method('commit')->willReturnSelf();
        $this->model->update($prices);
    }

    /**
     * Test update method throws exception.
     */
    public function testUpdateWithException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $this->expectExceptionMessage('Could not save Prices.');
        $attributeId = 5;
        $prices = [
            [
                'store_id' => 1,
                'row_id' => 1,
                'value' => 15
            ]
        ];
        $this->attributeRepository->expects($this->once())->method('get')->willReturn($this->productAttribute);
        $this->productAttribute->expects($this->once())->method('getAttributeId')->willReturn($attributeId);
        $this->attributeResource->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->connection);
        $this->connection->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $this->attributeResource
            ->expects($this->once())
            ->method('getTable')
            ->with('catalog_product_entity_decimal')
            ->willReturn('catalog_product_entity_decimal');
        $this->connection
            ->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                'catalog_product_entity_decimal',
                [
                    [
                        'store_id' => 1,
                        'row_id' => 1,
                        'value' => 15,
                        'attribute_id' => 5,
                    ]
                ],
                ['value']
            )
            ->willReturnSelf();
        $this->connection->expects($this->once())->method('commit')->willThrowException(new \Exception());
        $this->connection->expects($this->once())->method('rollback')->willReturnSelf();
        $this->model->update($prices);
    }

    /**
     * Test delete method.
     *
     * @return void
     */
    public function testDelete()
    {
        $attributeId = 5;
        $skus = ['sku_1', 'sku_2'];
        $idsBySku = [
            'sku_1' => [
                1 => Type::TYPE_SIMPLE
            ],
            'sku_2' => [
                2 => Type::TYPE_VIRTUAL
            ]
        ];
        $this->productIdLocator
            ->expects($this->once())
            ->method('retrieveProductIdsBySkus')->with($skus)
            ->willReturn($idsBySku);
        $this->attributeRepository->expects($this->once())->method('get')->willReturn($this->productAttribute);
        $this->productAttribute->expects($this->once())->method('getAttributeId')->willReturn($attributeId);
        $this->attributeResource->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->connection);
        $this->connection->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $this->attributeResource
            ->expects($this->once())
            ->method('getTable')
            ->with('catalog_product_entity_decimal')
            ->willReturn('catalog_product_entity_decimal');
        $this->connection
            ->expects($this->once())
            ->method('delete')
            ->with(
                'catalog_product_entity_decimal',
                [
                    'attribute_id = ?' => $attributeId,
                    'row_id IN (?)' => [1, 2]
                ]
            )
            ->willReturnSelf();
        $this->connection->expects($this->once())->method('commit')->willReturnSelf();
        $this->metadataPool->expects($this->atLeastOnce())->method('getMetadata')->willReturnSelf();
        $this->metadataPool->expects($this->atLeastOnce())->method('getLinkField')->willReturn('row_id');
        $this->model->delete($skus);
    }

    /**
     * Test delete method throws exception.
     */
    public function testDeleteWithException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotDeleteException');
        $this->expectExceptionMessage('Could not delete Prices');
        $attributeId = 5;
        $skus = ['sku_1', 'sku_2'];
        $idsBySku = [
            'sku_1' => [
                1 => Type::TYPE_SIMPLE
            ],
            'sku_2' => [
                2 => Type::TYPE_VIRTUAL
            ]
        ];
        $this->productIdLocator
            ->expects($this->once())
            ->method('retrieveProductIdsBySkus')->with($skus)
            ->willReturn($idsBySku);
        $this->attributeRepository->expects($this->once())->method('get')->willReturn($this->productAttribute);
        $this->productAttribute->expects($this->once())->method('getAttributeId')->willReturn($attributeId);
        $this->attributeResource->expects($this->atLeastOnce())->method('getConnection')
            ->willReturn($this->connection);
        $this->connection->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $this->attributeResource
            ->expects($this->once())
            ->method('getTable')
            ->with('catalog_product_entity_decimal')
            ->willReturn('catalog_product_entity_decimal');
        $this->connection
            ->expects($this->once())
            ->method('delete')
            ->with(
                'catalog_product_entity_decimal',
                [
                    'attribute_id = ?' => $attributeId,
                    'row_id IN (?)' => [1, 2]
                ]
            )
            ->willReturnSelf();
        $this->connection->expects($this->once())->method('commit')->willThrowException(new \Exception());
        $this->connection->expects($this->once())->method('rollBack')->willReturnSelf();
        $this->metadataPool->expects($this->atLeastOnce())->method('getMetadata')->willReturnSelf();
        $this->metadataPool->expects($this->atLeastOnce())->method('getLinkField')->willReturn('row_id');
        $this->model->delete($skus);
    }

    /**
     * Test retrieveSkuById method.
     *
     * @param int|null $expectedResult
     * @param int $id
     * @param array $skus
     * @dataProvider dataProviderRetrieveSkuById
     */
    public function testRetrieveSkuById($expectedResult, $id, array $skus)
    {
        $this->productIdLocator
            ->expects($this->once())
            ->method('retrieveProductIdsBySkus')
            ->willReturn($skus);

        $this->assertEquals($expectedResult, $this->model->retrieveSkuById($id, $skus));
    }

    /**
     * Data provider for retrieveSkuById  method.
     *
     * @return array
     */
    public function dataProviderRetrieveSkuById()
    {
        return [
            [
                null,
                2,
                ['sku_1' => [1 => 1]]
            ],
            [
                'sku_1',
                1,
                ['sku_1' => [1 => 1]]
            ],
            [
                null,
                1,
                ['sku_1' => [2 => 1]]
            ],
        ];
    }
}
