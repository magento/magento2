<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Factory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Edit\WeightResolver;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\TypeTransitionManager;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\UniqueValidationInterface;
use Magento\Eav\Model\Entity\Context;
use Magento\Eav\Model\Entity\Type as EntityType;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ActionTest extends TestCase
{
    private const ENTITY_IDS = [1, 2, 5, 10];
    private const STUB_PRIMARY_KEY = 'PK';

    /**
     * @var Action
     */
    private $model;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Factory|MockObject
     */
    private $factoryMock;

    /**
     * @var UniqueValidationInterface|MockObject
     */
    private $uniqueValidatorMock;

    /**
     * @var ProductCollectionFactory|MockObject
     */
    private $productCollectionFactoryMock;

    /**
     * @var TypeTransitionManager|MockObject
     */
    private $typeTransitionManagerMock;

    /**
     * @var DateTime|MockObject
     */
    private $dateTimeMock;

    /**
     * @var Config|MockObject
     */
    private $eavConfigMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var EntityType|MockObject
     */
    private $entityTypeMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var ProductCollection|MockObject
     */
    private $productCollectionMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->factoryMock = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->uniqueValidatorMock = $this->getMockBuilder(UniqueValidationInterface::class)
            ->getMockForAbstractClass();
        $this->productCollectionFactoryMock = $this->getMockBuilder(ProductCollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeTransitionManagerMock = $this->createPartialMock(
            TypeTransitionManager::class,
            ['processProduct']
        );
        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eavConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityTypeMock = $this->getMockBuilder(EntityType::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->method('getEavConfig')
            ->willReturn($this->eavConfigMock);
        $this->contextMock->method('getResource')
            ->willReturn($this->resourceMock);
        $this->eavConfigMock->method('getEntityType')
            ->willReturn($this->entityTypeMock);
        $updatedAtAttributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->eavConfigMock->method('getAttribute')
            ->willReturn($updatedAtAttributeMock);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Action::class,
            [
                'context' => $this->contextMock,
                'storeManager' => $this->storeManagerMock,
                'modelFactory' => $this->factoryMock,
                'uniqueValidator' => $this->uniqueValidatorMock,
                'dateTime' => $this->dateTimeMock,
                'productCollectionFactory' => $this->productCollectionFactoryMock,
                'typeTransitionManager' => $this->typeTransitionManagerMock,
                'data' => []
            ]
        );
    }

    private function prepareAdapter()
    {
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
        $this->resourceMock->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->resourceMock->method('getTableName')
            ->willReturn('catalog_product_entity');
    }

    private function prepareProductCollection($items)
    {
        $this->productCollectionMock = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productCollectionMock->method('addIdFilter')
            ->with(static::ENTITY_IDS)
            ->willReturnSelf();
        $this->productCollectionMock->method('addFieldToFilter')
            ->willReturnSelf();
        $this->productCollectionMock->method('addFieldToSelect')
            ->willReturnSelf();
        $this->productCollectionMock->method('getItems')
            ->willReturn($items);
        $this->productCollectionFactoryMock->method('create')
            ->willReturn($this->productCollectionMock);
    }

    /**
     * @param int $hasWeight
     * @param string $typeId
     * @param Product[] $items
     * @param int[] $entityIds
     * @dataProvider updateProductHasWeightAttributesDataProvider
     */
    public function testUpdateProductHasWeightAttributes($hasWeight, $typeId, $items, $entityIds)
    {
        $this->prepareAdapter();
        $this->prepareProductCollection($items);
        $attrData = [
            ProductAttributeInterface::CODE_HAS_WEIGHT => $hasWeight
        ];
        $storeId = 0;

        $this->connectionMock->method('getPrimaryKeyName')->willReturn(self::STUB_PRIMARY_KEY);
        $this->connectionMock->method('getIndexList')
            ->willReturn(
                [
                    self::STUB_PRIMARY_KEY => [
                        'COLUMNS_LIST' => ['Column']
                    ]
                ]
            );

        $this->connectionMock->expects($this->once())
            ->method('update')
            ->with(
                'catalog_product_entity',
                ['type_id' => $typeId],
                ['entity_id IN (?)' => $entityIds]
            );

        $this->model->updateAttributes(static::ENTITY_IDS, $attrData, $storeId);
    }

    /**
     * Update Attributes data provider
     *
     * @return array
     */
    public function updateProductHasWeightAttributesDataProvider()
    {
        return [
            [
                WeightResolver::HAS_WEIGHT,
                Type::TYPE_SIMPLE,
                $this->getProductsVirtualToSimple(),
                static::ENTITY_IDS
            ],
            [
                WeightResolver::HAS_NO_WEIGHT,
                Type::TYPE_VIRTUAL,
                $this->getProductsSimpleToVirtual(),
                static::ENTITY_IDS
            ],
            [
                WeightResolver::HAS_NO_WEIGHT,
                Type::TYPE_VIRTUAL,
                $this->getProductsMixedTypes(),
                array_slice(static::ENTITY_IDS, 2, 2)
            ]
        ];
    }

    private function getProductsSimpleToVirtual()
    {
        $result = [];

        foreach (static::ENTITY_IDS as $entityId) {
            $productMock = $this->getMockBuilder(Product::class)
                ->disableOriginalConstructor()
                ->getMock();
            $productMock->method('getId')
                ->willReturn($entityId);
            $productMock->expects($this->at(1))
                ->method('getTypeId')
                ->willReturn(Type::TYPE_SIMPLE);
            $productMock->expects($this->at(2))
                ->method('getTypeId')
                ->willReturn(Type::TYPE_VIRTUAL);
            $productMock->expects($this->at(3))
                ->method('getTypeId')
                ->willReturn(Type::TYPE_VIRTUAL);

            $result[] = $productMock;
        }

        return $result;
    }

    private function getProductsVirtualToSimple()
    {
        $result = [];

        foreach (static::ENTITY_IDS as $entityId) {
            $productMock = $this->getMockBuilder(Product::class)
                ->disableOriginalConstructor()
                ->getMock();
            $productMock->method('getId')
                ->willReturn($entityId);
            $productMock->expects($this->at(1))
                ->method('getTypeId')
                ->willReturn(Type::TYPE_VIRTUAL);
            $productMock->expects($this->at(2))
                ->method('getTypeId')
                ->willReturn(Type::TYPE_SIMPLE);
            $productMock->expects($this->at(3))
                ->method('getTypeId')
                ->willReturn(Type::TYPE_SIMPLE);

            $result[] = $productMock;
        }

        return $result;
    }

    private function getProductsMixedTypes()
    {
        $result = [];

        $i = 0;
        foreach (static::ENTITY_IDS as $entityId) {
            $productMock = $this->getMockBuilder(Product::class)
                ->disableOriginalConstructor()
                ->getMock();
            $productMock->method('getId')
                ->willReturn($entityId);

            if ($i < 2) {
                $productMock->expects($this->at(1))
                    ->method('getTypeId')
                    ->willReturn(Type::TYPE_SIMPLE);
                $productMock->expects($this->at(2))
                    ->method('getTypeId')
                    ->willReturn(Type::TYPE_SIMPLE);
            } else {
                $productMock->expects($this->at(1))
                    ->method('getTypeId')
                    ->willReturn(Type::TYPE_SIMPLE);
                $productMock->expects($this->at(2))
                    ->method('getTypeId')
                    ->willReturn(Type::TYPE_VIRTUAL);
                $productMock->expects($this->at(3))
                    ->method('getTypeId')
                    ->willReturn(Type::TYPE_VIRTUAL);
            }

            $result[] = $productMock;
            $i++;
        }

        return $result;
    }
}
