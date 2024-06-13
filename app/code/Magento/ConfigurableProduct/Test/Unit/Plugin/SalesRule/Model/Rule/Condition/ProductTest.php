<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\SalesRule\Model\Rule\Condition;

use Magento\Backend\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Test\Unit\Helper\ProductTestHelper;
use Magento\Quote\Test\Unit\Helper\AbstractItemTestHelper;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Plugin\SalesRule\Model\Rule\Condition\Product as ValidatorPlugin;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Locale\Format;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Rule\Model\Condition\Context;
use Magento\SalesRule\Model\Rule\Condition\Product as SalesRuleProduct;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Model\ProductCategoryList;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class ProductTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var SalesRuleProduct
     */
    private $validator;

    /**
     * @var \Magento\ConfigurableProduct\Plugin\SalesRule\Model\Rule\Condition\Product
     */
    private $validatorPlugin;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->validator = $this->createValidator();
        $this->validatorPlugin = $this->objectManager->getObject(ValidatorPlugin::class);
    }

    /**
     * @return \Magento\SalesRule\Model\Rule\Condition\Product
     */
    private function createValidator(): SalesRuleProduct
    {
        /** @var Context|MockObject $contextMock */
        $contextMock = $this->createMock(Context::class);
        /** @var Data|MockObject $backendHelperMock */
        $backendHelperMock = $this->createMock(Data::class);
        /** @var Config|MockObject $configMock */
        $configMock = $this->createMock(Config::class);
        /** @var ProductFactory|MockObject $productFactoryMock */
        $productFactoryMock = $this->createMock(ProductFactory::class);
        /** @var ProductRepositoryInterface|MockObject $productRepositoryMock */
        $productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $attributeLoaderInterfaceMock = $this->createPartialMock(AbstractEntity::class, ['getAttributesByCode']);
        $attributeLoaderInterfaceMock
            ->method('getAttributesByCode')->willReturn([]);
        /** @var Product|MockObject $productMock */
        $productMock = $this->createPartialMock(Product::class, ['loadAllAttributes', 'getConnection', 'getTable']);
        $productMock->method('loadAllAttributes')->willReturn($attributeLoaderInterfaceMock);
        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->createMock(Collection::class);
        /** @var FormatInterface|MockObject $formatMock */
        $formatMock = new Format(
            $this->createMock(ScopeResolverInterface::class),
            $this->createMock(ResolverInterface::class),
            $this->createMock(CurrencyFactory::class)
        );

        $productCategoryList = $this->createMock(ProductCategoryList::class);

        return new SalesRuleProduct(
            $contextMock,
            $backendHelperMock,
            $configMock,
            $productFactoryMock,
            $productRepositoryMock,
            $productMock,
            $collectionMock,
            $formatMock,
            [],
            $productCategoryList
        );
    }

    public function testChildIsUsedForValidation()
    {
        $item = $this->configurableProductTestSetUp();
        $item->expects($this->once())->method('setProduct');
        $this->validator->setAttribute('special_price');
        $this->validatorPlugin->beforeValidate($this->validator, $item);
    }

    public function configurableProductTestSetUp()
    {
        $configurableProductMock = $this->createPartialMock(ProductTestHelper::class, ['getTypeId', 'hasData']);
        $configurableProductMock->expects($this->any())->method('getTypeId')->willReturn(Configurable::TYPE_CODE);
        $configurableProductMock->expects($this->any())->method('hasData')->with('special_price')->willReturn(false);

        /* @var AbstractItemTestHelper|MockObject $item */
        $item = $this->createPartialMock(AbstractItemTestHelper::class, ['setProduct', 'getProduct', 'getChildren']);
        $item->expects($this->any())->method('getProduct')->willReturn($configurableProductMock);

        $simpleProductMock = $this->createPartialMock(ProductTestHelper::class, ['getTypeId', 'hasData']);
        $simpleProductMock->expects($this->any())->method('getTypeId')->willReturn(Type::TYPE_SIMPLE);
        $simpleProductMock->expects($this->any())->method('hasData')->with('special_price')->willReturn(true);

        $childItem = $this->createMock(AbstractItem::class);
        $childItem->expects($this->any())->method('getProduct')->willReturn($simpleProductMock);

        $item->expects($this->any())->method('getChildren')->willReturn([$childItem]);

        return $item;
    }

    public function testChildIsNotUsedForValidation()
    {
        $item = $this->configurableProductTestSetUp();
        $item->expects($this->never())->method('setProduct');
        $this->validator->setAttribute('special_price');
        $this->validator->setAttributeScope('parent');
        $this->validatorPlugin->beforeValidate($this->validator, $item);
    }

    /**
     * Test for Configurable product in invalid state with no children does not raise error
     */
    public function testChildIsNotUsedForValidationWhenConfigurableProductIsMissingChildren()
    {
        $configurableProductMock = $this->createPartialMock(ProductTestHelper::class, ['getTypeId', 'hasData']);
        $configurableProductMock->expects($this->any())->method('getTypeId')->willReturn(Configurable::TYPE_CODE);
        $configurableProductMock->expects($this->any())->method('hasData')->with('special_price')->willReturn(false);

        /* @var AbstractItemTestHelper|MockObject $item */
        $item = $this->createPartialMock(AbstractItemTestHelper::class, ['setProduct', 'getProduct', 'getChildren']);
        $item->expects($this->any())->method('getProduct')->willReturn($configurableProductMock);
        $item->expects($this->any())->method('getChildren')->willReturn([]);

        $this->validator->setAttribute('special_price');
        $item->expects($this->never())->method('setProduct');
        $this->validatorPlugin->beforeValidate($this->validator, $item);
    }
}
