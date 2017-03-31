<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Price;

/**
 * Class PricePersistenceTest.
 */
class PricePersistenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeResource;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeRepository;

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productAttribute;

    /**
     * @var \Magento\Catalog\Model\ProductIdLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productIdLocator;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var \Magento\Catalog\Model\Product\Price\PricePersistence
     */
    private $model;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->attributeResource = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Attribute::class)
            ->disableOriginalConstructor()->getMock();
        $this->attributeRepository = $this->getMockBuilder(
            \Magento\Catalog\Api\ProductAttributeRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productIdLocator = $this->getMockBuilder(\Magento\Catalog\Model\ProductIdLocatorInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->metadataPool = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLinkField', 'getMetadata'])
            ->getMock();
        $this->connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->productAttribute = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductAttributeInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\Product\Price\PricePersistence::class,
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
            'sku_1' =>
                [
                    1 => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
                ],
            'sku_2' =>
                [
                    2 => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL
                ]
        ];
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()->getMock();
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
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save Prices.
     */
    public function testUpdateWithException()
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
            'sku_1' =>
                [
                    1 => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
                ],
            'sku_2' =>
                [
                    2 => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL
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
     *
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage Could not delete Prices
     */
    public function testDeleteWithException()
    {
        $attributeId = 5;
        $skus = ['sku_1', 'sku_2'];
        $idsBySku = [
            'sku_1' =>
                [
                    1 => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
                ],
            'sku_2' =>
                [
                    2 => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL
                ]
        ];
        $this->productIdLocator
            ->expects($this->once())
            ->method('retrieveProductIdsBySkus')->with($skus)
            ->willReturn($idsBySku);
        $this->attributeRepository->expects($this->once())->method('get')->willReturn($this->productAttribute);
        $this->productAttribute->expects($this->once())->method('getAttributeId')->willReturn($attributeId);
        $this->attributeResource->expects($this->atLeastOnce(2))->method('getConnection')
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
