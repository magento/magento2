<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\SalesRule\Model\Rule\Condition;

use Magento\Backend\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product;
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
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Data|MockObject $backendHelperMock */
        $backendHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Config|MockObject $configMock */
        $configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var ProductFactory|MockObject $productFactoryMock */
        $productFactoryMock = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var ProductRepositoryInterface|MockObject $productRepositoryMock */
        $productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->getMockForAbstractClass();
        $attributeLoaderInterfaceMock = $this->getMockBuilder(AbstractEntity::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttributesByCode'])
            ->getMock();
        $attributeLoaderInterfaceMock
            ->expects($this->any())
            ->method('getAttributesByCode')
            ->willReturn([]);
        /** @var Product|MockObject $productMock */
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadAllAttributes', 'getConnection', 'getTable'])
            ->getMock();
        $productMock->expects($this->any())
            ->method('loadAllAttributes')
            ->willReturn($attributeLoaderInterfaceMock);
        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var FormatInterface|MockObject $formatMock */
        $formatMock = new Format(
            $this->getMockBuilder(ScopeResolverInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass(),
            $this->getMockBuilder(ResolverInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass(),
            $this->getMockBuilder(CurrencyFactory::class)
                ->disableOriginalConstructor()
                ->getMock()
        );

        $productCategoryList = $this->getMockBuilder(ProductCategoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

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

    /**
     * @return Product|MockObject
     */
    private function createProductMock(): MockObject
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['getAttribute', 'setQuoteItemQty', 'setQuoteItemPrice'])
            ->onlyMethods(
                [
                    'getId',
                    'getTypeId',
                    'hasData',
                ]
            )
            ->getMock();
        $productMock
            ->expects($this->any())
            ->method('setQuoteItemQty')
            ->willReturnSelf();
        $productMock
            ->expects($this->any())
            ->method('setQuoteItemPrice')
            ->willReturnSelf();

        return $productMock;
    }

    public function configurableProductTestSetUp()
    {
        $configurableProductMock = $this->createProductMock();
        $configurableProductMock
            ->expects($this->any())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);
        $configurableProductMock
            ->expects($this->any())
            ->method('hasData')
            ->with('special_price')
            ->willReturn(false);

        /* @var AbstractItem|MockObject $item */
        $item = $this->getMockBuilder(AbstractItem::class)
            ->disableOriginalConstructor()
            ->addMethods(['setProduct'])
            ->onlyMethods(['getProduct', 'getChildren'])
            ->getMockForAbstractClass();
        $item->expects($this->any())
            ->method('getProduct')
            ->willReturn($configurableProductMock);

        $simpleProductMock = $this->createProductMock();
        $simpleProductMock
            ->expects($this->any())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_SIMPLE);
        $simpleProductMock
            ->expects($this->any())
            ->method('hasData')
            ->with('special_price')
            ->willReturn(true);

        $childItem = $this->getMockBuilder(AbstractItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProduct'])
            ->getMockForAbstractClass();
        $childItem->expects($this->any())
            ->method('getProduct')
            ->willReturn($simpleProductMock);

        $item->expects($this->any())
            ->method('getChildren')
            ->willReturn([$childItem]);

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
        $configurableProductMock = $this->createProductMock();
        $configurableProductMock
            ->expects($this->any())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $configurableProductMock
            ->expects($this->any())
            ->method('hasData')
            ->with('special_price')
            ->willReturn(false);

        /* @var AbstractItem|MockObject $item */
        $item = $this->getMockBuilder(AbstractItem::class)
            ->disableOriginalConstructor()
            ->addMethods(['setProduct'])
            ->onlyMethods(['getProduct', 'getChildren'])
            ->getMockForAbstractClass();
        $item->expects($this->any())
            ->method('getProduct')
            ->willReturn($configurableProductMock);
        $item->expects($this->any())
            ->method('getChildren')
            ->willReturn([]);

        $this->validator->setAttribute('special_price');
        $item->expects($this->never())->method('setProduct');
        $this->validatorPlugin->beforeValidate($this->validator, $item);
    }
}
