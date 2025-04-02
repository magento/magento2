<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Rule\Condition;

use Magento\Backend\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductCategoryList;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\AttributeLoaderInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Locale\Format;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Rule\Model\Condition\Context;
use Magento\SalesRule\Model\Rule\Condition\Product as SalesRuleProduct;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends TestCase
{
    private const STUB_CATEGORY_ID = 5;
    /** @var SalesRuleProduct */
    protected $model;

    /** @var Context|MockObject */
    protected $contextMock;

    /** @var Data|MockObject */
    protected $backendHelperMock;

    /** @var Config|MockObject */
    protected $configMock;

    /** @var ProductFactory|MockObject */
    protected $productFactoryMock;

    /** @var ProductRepositoryInterface|MockObject */
    protected $productRepositoryMock;

    /** @var Product|MockObject */
    protected $productMock;

    /** @var Collection|MockObject */
    protected $collectionMock;

    /** @var FormatInterface */
    protected $format;

    /** @var AttributeLoaderInterface|MockObject */
    protected $attributeLoaderInterfaceMock;

    /** @var AdapterInterface|MockObject */
    protected $adapterInterfaceMock;

    /** @var Select|MockObject */
    protected $selectMock;

    /** @var MockObject|ProductCategoryList */
    private $productCategoryListMock;

    /**
     * Setup the test
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productFactoryMock = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->attributeLoaderInterfaceMock = $this->getMockBuilder(AbstractEntity::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttributesByCode'])
            ->getMock();
        $this->attributeLoaderInterfaceMock
            ->expects($this->any())
            ->method('getAttributesByCode')
            ->willReturn([]);
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['distinct', 'from', 'where'])
            ->getMock();
        $this->selectMock
            ->expects($this->any())
            ->method('distinct')
            ->willReturnSelf();
        $this->selectMock
            ->expects($this->any())
            ->method('from')
            ->with($this->anything(), $this->anything())
            ->willReturnSelf();
        $this->adapterInterfaceMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchCol', 'select'])
            ->getMockForAbstractClass();
        $this->adapterInterfaceMock
            ->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadAllAttributes', 'getConnection', 'getTable'])
            ->getMock();
        $this->productMock
            ->expects($this->any())
            ->method('loadAllAttributes')
            ->willReturn($this->attributeLoaderInterfaceMock);
        $this->productMock
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->adapterInterfaceMock);
        $this->productMock
            ->expects($this->any())
            ->method('getTable')
            ->with($this->anything())
            ->willReturn('table_name');
        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productCategoryListMock = $this->getMockBuilder(ProductCategoryList::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCategoryIds'])
            ->getMock();
        $this->format = new Format(
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

        $this->model = new SalesRuleProduct(
            $this->contextMock,
            $this->backendHelperMock,
            $this->configMock,
            $this->productFactoryMock,
            $this->productRepositoryMock,
            $this->productMock,
            $this->collectionMock,
            $this->format,
            [],
            $this->productCategoryListMock
        );
    }

    /**
     * @return array
     */
    public static function getValueElementChooserUrlDataProvider()
    {
        return [
            'category_ids_without_js_object' => [
                'category_ids',
                'sales_rule/promo_widget/chooser/attribute/'
            ],
            'category_ids_with_js_object' => [
                'category_ids',
                'sales_rule/promo_widget/chooser/attribute/',
                'jsobject'
            ],
            'sku_without_js_object' => [
                'sku',
                'sales_rule/promo_widget/chooser/attribute/',
                'jsobject'
            ],
            'sku_without_with_js_object' => [
                'sku',
                'sales_rule/promo_widget/chooser/attribute/'
            ],
            'none' => [
                '',
                ''
            ]
        ];
    }

    /**
     * test getValueElementChooserUrl
     * @param string $attribute
     * @param string $url
     * @param string $jsObject
     * @dataProvider getValueElementChooserUrlDataProvider
     */
    public function testGetValueElementChooserUrl($attribute, $url, $jsObject = '')
    {
        $this->model->setJsFormObject($jsObject);
        $this->model->setAttribute($attribute);
        $url .= $this->model->getAttribute();
        $this->backendHelperMock
            ->expects($this->any())
            ->method('getUrl')
            ->willReturnArgument(0);

        if ($this->model->getJsFormObject()) {
            $url .= '/form/' . $this->model->getJsFormObject();
        }

        $this->assertEquals($url, $this->model->getValueElementChooserUrl());
    }

    /**
     * test ValidateCategoriesIgnoresVisibility
     */
    public function testValidateCategoriesIgnoresVisibility(): void
    {
        /* @var \Magento\Catalog\Model\Product|MockObject $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->addMethods(['getAttribute', 'setQuoteItemQty', 'setQuoteItemPrice'])
            ->getMock();
        $product
            ->method('setQuoteItemQty')
            ->willReturnSelf();
        $product
            ->method('setQuoteItemPrice')
            ->willReturnSelf();
        /* @var AbstractItem|MockObject $item */
        $item = $this->getMockBuilder(AbstractItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProduct'])
            ->getMockForAbstractClass();
        $item->expects($this->any())
            ->method('getProduct')
            ->willReturn($product);
        $this->model->setAttribute('category_ids');
        $this->productCategoryListMock->method('getCategoryIds')
            ->willReturn([self::STUB_CATEGORY_ID]);
        $this->model->validate($item);
    }

    /**
     * @param boolean $isValid
     * @param string $conditionValue
     * @param string $operator
     * @param string $productPrice
     * @dataProvider localisationProvider
     */
    public function testQuoteLocaleFormatPrice($isValid, $conditionValue, $operator = '>=', $productPrice = '2000.00')
    {
        $attr = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->addMethods(['getAttribute'])
            ->getMockForAbstractClass();

        $attr->expects($this->any())
            ->method('getAttribute')
            ->willReturn('');

        /* @var \Magento\Catalog\Model\Product|MockObject $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['setQuoteItemPrice'])
            ->onlyMethods(['getResource', 'hasData', 'getData'])
            ->getMock();

        $product->expects($this->any())
            ->method('setQuoteItemPrice')
            ->willReturnSelf();

        $product->expects($this->any())
            ->method('getResource')
            ->willReturn($attr);

        $product->expects($this->any())
            ->method('hasData')
            ->willReturn(true);

        $product->expects($this->any())
            ->method('getData')
            ->with('quote_item_price')
            ->willReturn($productPrice);

        /* @var AbstractItem|MockObject $item */
        $item = $this->getMockBuilder(AbstractItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPrice', 'getProduct'])
            ->getMockForAbstractClass();

        $item->expects($this->any())
            ->method('getPrice')
            ->willReturn($productPrice);

        $item->expects($this->any())
            ->method('getProduct')
            ->willReturn($product);

        $this->model->setAttribute('quote_item_price');
        $this->model->setData('operator', $operator);

        $this->assertEquals($isValid, $this->model->setValue($conditionValue)->validate($item));
    }

    /**
     * DataProvider for testQuoteLocaleFormatPrice
     *
     * @return array
     */
    public static function localisationProvider(): array
    {
        return [
            'number' => [true, 500.01],
            'locale' => [true, '1,500.03'],
            'operation' => [true, '1,500.03', '!='],
            'stringOperation' => [false, '1,500.03', '{}'],
            'smallPrice' => [false, '1,500.03', '>=', 1000],
        ];
    }

    public function testValidateWhenAttributeValueIsMissingInTheProduct(): void
    {
        $attributeCode = 'test_attr';
        $attribute = new DataObject();
        $attribute->setBackendType('varchar');
        $attribute->setFrontendInput('text');

        $newResource = $this->createPartialMock(Product::class, ['getAttribute']);
        $newResource->expects($this->any())
            ->method('getAttribute')
            ->with($attributeCode)
            ->willReturn($attribute);

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'load', 'getResource'])
            ->getMock();
        $product->method('getId')
            ->willReturn(1);
        $product->expects($this->never())
            ->method('load')
            ->willReturnSelf();
        $product->expects($this->atLeastOnce())
            ->method('getResource')
            ->willReturn($newResource);

        $item = $this->createMock(AbstractItem::class);
        $item->expects($this->any())
            ->method('getProduct')
            ->willReturn($product);
        $this->model->setAttribute($attributeCode);
        $this->model->validate($item);
    }
}
