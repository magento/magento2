<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\ResourceModel\Product\Type;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Relation as ProductRelation;
use Magento\ConfigurableProduct\Model\AttributeOptionProvider;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionProvider;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableTest extends TestCase
{
    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var ProductRelation|MockObject
     */
    private $relation;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var AbstractAttribute|MockObject
     */
    private $abstractAttribute;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var AttributeOptionProvider|MockObject
     */
    private $attributeOptionProvider;

    /**
     * @var OptionProvider|MockObject
     */
    private $optionProvider;

    /**
     * @var ScopeResolverInterface|MockObject
     */
    private $scopeResolver;

    /**
     * @var int
     */
    private $storeId = 1;

    protected function setUp(): void
    {
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->resource = $this->createPartialMock(ResourceConnection::class, ['getConnection', 'getTableName']);

        $this->relation = $this->createMock(ProductRelation::class);
        $this->scopeResolver = $this->createMock(ScopeResolverInterface::class);
        $scope = $this->createMock(ScopeInterface::class);
        $scope->method('getId')->willReturnCallback(fn () => $this->storeId);
        $this->scopeResolver->method('getScope')->willReturn($scope);
        $this->abstractAttribute = $this->createMock(AbstractAttribute::class);
        $this->product = $this->createPartialMock(Product::class, ['__sleep', 'getData']);
        $this->attributeOptionProvider = $this->createMock(AttributeOptionProvider::class);
        $this->optionProvider = $this->createMock(OptionProvider::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $context = $this->createPartialMock(Context::class, ['getResources']);
        $context->expects($this->once())->method('getResources')->willReturn($this->resource);

        $this->configurable = $this->objectManagerHelper->getObject(
            Configurable::class,
            [
                'catalogProductRelation' => $this->relation,
                'scopeResolver' => $this->scopeResolver,
                'attributeOptionProvider' => $this->attributeOptionProvider,
                'optionProvider' => $this->optionProvider,
                'context' => $context
            ]
        );
    }

    public function testSaveProducts()
    {
        $this->product->expects($this->once())
            ->method('getData')
            ->willReturn(3);
        $this->optionProvider->expects($this->once())
            ->method('getProductEntityLinkField')
            ->willReturnSelf();
        $this->resource->method('getConnection')->willReturn($this->connectionMock);
        $this->resource->method('getTableName')->willReturn('table name');

        $select = $this->createPartialMock(Select::class, ['from', 'where']);
        $select->expects($this->exactly(1))->method('from')->willReturnSelf();
        $select->expects($this->exactly(1))->method('where')->willReturnSelf();

        $this->connectionMock->expects($this->atLeastOnce())
            ->method('select')
            ->willReturn($select);

        $existingProductIds = [1, 2];
        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($select)
            ->willReturn($existingProductIds);

        $this->connectionMock->expects($this->once())
            ->method('insertMultiple')
            ->with(
                'table name',
                [
                    ['product_id' => 3, 'parent_id' => 3],
                    ['product_id' => 4, 'parent_id' => 3],
                ]
            )
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('delete')
            ->with(
                'table name',
                ['parent_id = ?' => 3, 'product_id IN (?)' => [1]]
            )
            ->willReturnSelf();

        $this->assertSame(
            $this->configurable,
            $this->configurable->saveProducts($this->product, [2, 3, 4])
        );
    }

    public function testSaveProductsResetsAttributeOptions(): void
    {
        $this->abstractAttribute->method('getAttributeId')->willReturn(11);
        $original = [['sku' => 'original']];
        $updated = [['sku' => 'updated']];
        $this->attributeOptionProvider->expects($this->exactly(2))
            ->method('getAttributeOptions')
            ->with($this->abstractAttribute, 3)
            ->willReturn($original, $updated);
        $this->product->method('getData')->with('link')->willReturn(3);
        $this->optionProvider->method('getProductEntityLinkField')->willReturn('link');
        $this->resource->method('getConnection')->willReturn($this->connectionMock);
        $this->resource->method('getTableName')->willReturn('catalog_product_super_link');
        $select = $this->createPartialMock(Select::class, ['from', 'where']);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $this->connectionMock->method('select')->willReturn($select);
        $this->connectionMock->method('fetchCol')->with($select)->willReturn([]);
        $this->connectionMock->expects($this->never())->method('insertMultiple');
        $this->connectionMock->expects($this->never())->method('delete');
        $this->relation->expects($this->once())->method('processRelations')->with(3, []);

        $this->assertSame($original, $this->configurable->getAttributeOptions($this->abstractAttribute, 3));
        $this->assertSame($original, $this->configurable->getAttributeOptions($this->abstractAttribute, 3));
        $this->configurable->saveProducts($this->product, []);
        $this->assertSame($updated, $this->configurable->getAttributeOptions($this->abstractAttribute, 3));
    }

    public function testGetConfigurableOptions()
    {
        $this->product->expects($this->once())
            ->method('getData')
            ->with('link')
            ->willReturn('getId value');

        $this->abstractAttribute->method('getBackendTable')->willReturn('getBackendTable value');
        $this->abstractAttribute->method('getAttributeId')->willReturn('getAttributeId value');
        $attributes = [
            $this->abstractAttribute,
        ];

        $this->optionProvider->expects($this->once())
            ->method('getProductEntityLinkField')
            ->willReturn('link');
        $this->attributeOptionProvider->expects($this->once())
            ->method('getAttributeOptions')
            ->willReturn('fetchAll value');

        $expectedAttributesOptionsData = [
            'getAttributeId value' => 'fetchAll value',
        ];
        $actualAttributesOptionsData = $this->configurable->getConfigurableOptions($this->product, $attributes);
        $this->assertEquals($expectedAttributesOptionsData, $actualAttributesOptionsData);
    }

    #[DataProvider('attributeOptionsProvider')]
    public function testGetConfigurableOptionsReusesLoadedOptions(array $options): void
    {
        $this->abstractAttribute->method('getAttributeId')->willReturn(11);
        $this->product->method('getData')->with('link')->willReturn(42);
        $this->optionProvider->method('getProductEntityLinkField')->willReturn('link');
        $this->attributeOptionProvider->expects($this->once())
            ->method('getAttributeOptions')
            ->with($this->abstractAttribute, 42)
            ->willReturn($options);

        $this->assertSame($options, $this->configurable->getAttributeOptions($this->abstractAttribute, 42));
        $this->assertSame(
            [11 => $options],
            $this->configurable->getConfigurableOptions($this->product, [$this->abstractAttribute])
        );
    }

    public static function attributeOptionsProvider(): array
    {
        return [
            'options' => [[['value_index' => '7', 'option_title' => 'Red', 'sku' => 'simple-red']]],
            'no options' => [[]],
        ];
    }

    public function testOptionsAreCachedSeparatelyForEachAttribute(): void
    {
        $this->abstractAttribute->method('getAttributeId')->willReturn(11);
        $otherAttribute = $this->createMock(AbstractAttribute::class);
        $otherAttribute->method('getAttributeId')->willReturn(12);
        $colorOptions = [['option_title' => 'Red']];
        $sizeOptions = [['option_title' => 'Small']];
        $this->attributeOptionProvider->expects($this->exactly(2))
            ->method('getAttributeOptions')
            ->willReturnMap([
                [$this->abstractAttribute, 42, $colorOptions],
                [$otherAttribute, 42, $sizeOptions],
            ]);

        $this->assertSame($colorOptions, $this->configurable->getAttributeOptions($this->abstractAttribute, 42));
        $this->assertSame($sizeOptions, $this->configurable->getAttributeOptions($otherAttribute, 42));
        $this->assertSame($colorOptions, $this->configurable->getAttributeOptions($this->abstractAttribute, 42));
        $this->assertSame($sizeOptions, $this->configurable->getAttributeOptions($otherAttribute, 42));
    }

    public function testOptionsAreReloadedWhenStoreChanges(): void
    {
        $this->abstractAttribute->method('getAttributeId')->willReturn(11);
        $labels = [1 => [['option_title' => 'Red']], 2 => [['option_title' => 'Rouge']]];
        $this->attributeOptionProvider->expects($this->exactly(3))
            ->method('getAttributeOptions')
            ->with($this->abstractAttribute, 42)
            ->willReturnCallback(function () use ($labels) {
                return $labels[$this->storeId];
            });

        foreach ([1, 2, 1] as $storeId) {
            $this->storeId = $storeId;
            $this->assertSame(
                $labels[$storeId],
                $this->configurable->getAttributeOptions($this->abstractAttribute, 42)
            );
            $this->assertSame(
                $labels[$storeId],
                $this->configurable->getAttributeOptions($this->abstractAttribute, 42)
            );
        }
    }

    public function testResetReloadsChangedOptions(): void
    {
        $this->abstractAttribute->method('getAttributeId')->willReturn(11);
        $original = [['option_title' => 'Red']];
        $updated = [['option_title' => 'Crimson']];
        $this->attributeOptionProvider->expects($this->exactly(2))
            ->method('getAttributeOptions')
            ->with($this->abstractAttribute, 42)
            ->willReturn($original, $updated);

        $this->assertSame($original, $this->configurable->getAttributeOptions($this->abstractAttribute, 42));
        $this->configurable->_resetState();
        $this->assertSame($updated, $this->configurable->getAttributeOptions($this->abstractAttribute, 42));
        $this->assertSame($updated, $this->configurable->getAttributeOptions($this->abstractAttribute, 42));
    }

    public function testChangingProductsDiscardsPreviousOptions(): void
    {
        $this->abstractAttribute->method('getAttributeId')->willReturn(11);
        $options = [42 => [['sku' => 'first']], 43 => [['sku' => 'second']]];
        $this->attributeOptionProvider->expects($this->exactly(3))
            ->method('getAttributeOptions')
            ->willReturnMap([
                [$this->abstractAttribute, 42, $options[42]],
                [$this->abstractAttribute, 43, $options[43]],
            ]);

        foreach ([42, 43, 42] as $productId) {
            $this->assertSame(
                $options[$productId],
                $this->configurable->getAttributeOptions($this->abstractAttribute, $productId)
            );
        }
    }
}
