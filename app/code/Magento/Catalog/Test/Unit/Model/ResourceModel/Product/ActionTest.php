<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use PHPUnit\Framework\Attributes\DataProvider;
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
use Magento\Eav\Model\Entity\AttributeLoaderInterface;
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

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $object = new ObjectManager($this);
        $objects = [
            [
                UniqueValidationInterface::class,
                $this->createMock(UniqueValidationInterface::class)
            ],
            [
                AttributeLoaderInterface::class,
                $this->createMock(AttributeLoaderInterface::class)
            ]
        ];
        $object->prepareObjectManager($objects);
        $this->contextMock = $this->createMock(Context::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->factoryMock = $this->createMock(Factory::class);
        $this->uniqueValidatorMock = $this->createMock(UniqueValidationInterface::class);
        $this->productCollectionFactoryMock = $this->createMock(ProductCollectionFactory::class);
        $this->typeTransitionManagerMock = $this->createPartialMock(
            TypeTransitionManager::class,
            ['processProduct']
        );
        $this->dateTimeMock = $this->createMock(DateTime::class);

        $this->eavConfigMock = $this->createMock(Config::class);
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->entityTypeMock = $this->createMock(EntityType::class);

        $this->contextMock->method('getEavConfig')
            ->willReturn($this->eavConfigMock);
        $this->contextMock->method('getResource')
            ->willReturn($this->resourceMock);
        $this->eavConfigMock->method('getEntityType')
            ->willReturn($this->entityTypeMock);
        $updatedAtAttributeMock = $this->createPartialMock(AbstractAttribute::class, []);
        $hasWeightAttributeMock = $this->createPartialMock(AbstractAttribute::class, []);
        $hasWeightAttributeMock->setData('attribute_code', 'has_weight');
        $hasWeightAttributeMock->setData('backend_type', 'int');
        $hasWeightAttributeMock->setData('is_global', 1);
        $this->eavConfigMock->method('getAttribute')
            ->willReturnCallback(
                function ($entityType, $attributeCode = null) use ($updatedAtAttributeMock, $hasWeightAttributeMock) {
                    $code = $attributeCode ?? $entityType;
                    if ($code === 'has_weight' || $code === ProductAttributeInterface::CODE_HAS_WEIGHT) {
                        return $hasWeightAttributeMock;
                    }
                    return $updatedAtAttributeMock;
                }
            );

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

    /**
     * @return void
     */
    private function prepareAdapter(): void
    {
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->resourceMock->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->resourceMock->method('getTableName')
            ->willReturn('catalog_product_entity');
    }

    /**
     * @param $items
     *
     * @return void
     */
    private function prepareProductCollection($items): void
    {
        $this->productCollectionMock = $this->createMock(ProductCollection::class);
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
     *
     * @return void
     */
    #[DataProvider('updateProductHasWeightAttributesDataProvider')]
    public function testUpdateProductHasWeightAttributes($hasWeight, $typeId, $items, $entityIds): void
    {
        $items = $items($this);
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
    public static function updateProductHasWeightAttributesDataProvider(): array
    {
        return [
            [
                WeightResolver::HAS_WEIGHT,
                Type::TYPE_SIMPLE,
                static fn (self $testCase) => $testCase->getProductsVirtualToSimple(),
                static::ENTITY_IDS
            ],
            [
                WeightResolver::HAS_NO_WEIGHT,
                Type::TYPE_VIRTUAL,
                static fn (self $testCase) => $testCase->getProductsSimpleToVirtual(),
                static::ENTITY_IDS
            ],
            [
                WeightResolver::HAS_NO_WEIGHT,
                Type::TYPE_VIRTUAL,
                static fn (self $testCase) => $testCase->getProductsMixedTypes(),
                array_slice(static::ENTITY_IDS, 2, 2)
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getProductsSimpleToVirtual(): array
    {
        $result = [];

        foreach (static::ENTITY_IDS as $entityId) {
            $productMock = $this->createMock(Product::class);
            $productMock->method('getId')->willReturn($entityId);
            $productMock->method('getTypeId')
                ->willReturnOnConsecutiveCalls(
                    Type::TYPE_SIMPLE,
                    Type::TYPE_VIRTUAL,
                    Type::TYPE_VIRTUAL
                );

            $result[] = $productMock;
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getProductsVirtualToSimple(): array
    {
        $result = [];

        foreach (static::ENTITY_IDS as $entityId) {
            $productMock = $this->createMock(Product::class);
            $productMock->method('getId')->willReturn($entityId);
            $productMock->method('getTypeId')
                ->willReturnOnConsecutiveCalls(
                    Type::TYPE_VIRTUAL,
                    Type::TYPE_SIMPLE,
                    Type::TYPE_SIMPLE
                );

            $result[] = $productMock;
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getProductsMixedTypes(): array
    {
        $result = [];

        $i = 0;
        foreach (static::ENTITY_IDS as $entityId) {
            $productMock = $this->createMock(Product::class);
            $productMock->method('getId')
                ->willReturn($entityId);

            if ($i < 2) {
                $productMock->method('getTypeId')
                    ->willReturn(Type::TYPE_SIMPLE);
            } else {
                $productMock->method('getTypeId')
                    ->willReturnOnConsecutiveCalls(
                        Type::TYPE_SIMPLE,
                        Type::TYPE_VIRTUAL,
                        Type::TYPE_VIRTUAL
                    );
            }

            $result[] = $productMock;
            $i++;
        }

        return $result;
    }
}
