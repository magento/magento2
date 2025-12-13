<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Rule\Condition;

use Magento\Backend\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Catalog\Model\ProductCategoryList;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Rule\Model\Condition\Context;
use Magento\SalesRule\Model\Rule\Condition\Product as SalesRuleProduct;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends TestCase
{
    use MockCreationTrait;
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
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
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
        $this->adapterInterfaceMock = $this->createMock(AdapterInterface::class);
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
            $this->createMock(ScopeResolverInterface::class),
            $this->createMock(ResolverInterface::class),
            $this->createMock(CurrencyFactory::class)
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
     */
    #[DataProvider('getValueElementChooserUrlDataProvider')]
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
        /* @var CatalogProduct|MockObject $product */
        $product = $this->createPartialMockWithReflection(
            CatalogProduct::class,
            ['getId', 'getAttribute', 'setQuoteItemQty', 'setQuoteItemPrice']
        );
        $product
            ->method('setQuoteItemQty')
            ->willReturnSelf();
        $product
            ->method('setQuoteItemPrice')
            ->willReturnSelf();
        /* @var AbstractItem|MockObject $item */
        $item = $this->createMock(AbstractItem::class);
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
     */
    #[DataProvider('localisationProvider')]
    public function testQuoteLocaleFormatPrice($isValid, $conditionValue, $operator = '>=', $productPrice = '2000.00')
    {
        $attr = $this->createPartialMock(
            Product::class,
            ['getAttribute']
        );
        $attr->method('getAttribute')->willReturn(null);

        /* @var CatalogProduct|MockObject $product */
        $product = $this->createPartialMockWithReflection(
            CatalogProduct::class,
            ['setQuoteItemPrice', 'getResource', 'hasData', 'getData']
        );

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
        $item = $this->createMock(AbstractItem::class);

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
     * Test for loadAttributeOptions
     *
     * @return void
     */
    public function testLoadAttributeOptions(): void
    {
        $secondAttributeCode = 'second_attribute';

        $attribute = $this->getMockBuilder(Attribute::class)
            ->onlyMethods(['getDataUsingMethod'])
            ->disableOriginalConstructor()
            ->getMock();
        $attribute->expects($this->atLeastOnce())
            ->method('getDataUsingMethod')
            ->with('is_used_for_promo_rules')
            ->willReturn(false);

        $attributeSecond = $this->createPartialMockWithReflection(
            Attribute::class,
            ['getDataUsingMethod', 'isAllowedForRuleCondition', 'getAttributeCode', 'getFrontendLabel']
        );
        $attributeSecond->expects($this->atLeastOnce())
            ->method('getDataUsingMethod')
            ->with('is_used_for_promo_rules')
            ->willReturn(true);
        $attributeSecond->expects($this->atLeastOnce())
            ->method('isAllowedForRuleCondition')
            ->willReturn(true);
        $attributeSecond->expects($this->atLeastOnce())
            ->method('getFrontendLabel')
            ->willReturn('Second Attribute');
        $attributeSecond->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn($secondAttributeCode);

        $attributeLoaderInterfaceMock = $this->createMock(AbstractEntity::class);
        $attributeLoaderInterfaceMock->expects($this->atLeastOnce())
            ->method('getAttributesByCode')
            ->willReturn([$attribute, $attributeSecond]);

        $productResourceMock = $this->createMock(Product::class);
        $productResourceMock->expects($this->atLeastOnce())
            ->method('loadAllAttributes')
            ->willReturn($attributeLoaderInterfaceMock);

        $model = new SalesRuleProduct(
            $this->contextMock,
            $this->backendHelperMock,
            $this->configMock,
            $this->productFactoryMock,
            $this->productRepositoryMock,
            $productResourceMock,
            $this->collectionMock,
            $this->format,
            [],
            $this->productCategoryListMock
        );

        $model->loadAttributeOptions();

        $this->assertArrayHasKey($secondAttributeCode, $model->getAttributeOption());
        $this->assertArrayHasKey('children::' . $secondAttributeCode, $model->getAttributeOption());
        $this->assertArrayHasKey('parent::' . $secondAttributeCode, $model->getAttributeOption());
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

    /**
     * Ensure price comes from parent item for configurables.
     */
    public function testQuoteItemPriceUsesParentItemPriceWhenPresent(): void
    {
        $parentUnitPrice = 100.0;
        $childUnitPrice = 0.0;

        $attr = $this->createPartialMock(Product::class, ['getAttribute']);
        $attr->method('getAttribute')->willReturn(
            new DataObject(
                ['frontend_input' => 'text', 'backend_type' => 'varchar']
            )
        );

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResource', 'hasData', 'getData'])
            ->addMethods(['setQuoteItemQty', 'setQuoteItemPrice', 'setQuoteItemRowTotal'])
            ->getMock();
        $product->method('getResource')->willReturn($attr);
        $product->method('hasData')->willReturn(true);
        $product->method('getData')->with('quote_item_price')->willReturn($parentUnitPrice);
        $product->method('setQuoteItemQty')->willReturnSelf();
        $product->expects($this->once())
            ->method('setQuoteItemPrice')
            ->with($this->equalTo($parentUnitPrice))
            ->willReturnSelf();
        $product->method('setQuoteItemRowTotal')->willReturnSelf();

        $parentItem = $this->getMockBuilder(AbstractItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQty', 'getPrice', 'getParentItem', 'getProduct'])
            ->addMethods(['getBaseRowTotal'])
            ->getMockForAbstractClass();
        $parentItem->method('getQty')->willReturn(1);
        $parentItem->method('getPrice')->willReturn($parentUnitPrice);
        $parentItem->method('getBaseRowTotal')->willReturn($parentUnitPrice);
        $parentItem->method('getParentItem')->willReturn(null);
        $parentItem->method('getProduct')->willReturn($product);

        $childItem = $this->getMockBuilder(AbstractItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQty', 'getPrice', 'getParentItem', 'getProduct'])
            ->addMethods(['getBaseRowTotal'])
            ->getMockForAbstractClass();
        $childItem->method('getQty')->willReturn(1);
        $childItem->method('getPrice')->willReturn($childUnitPrice);
        $childItem->method('getBaseRowTotal')->willReturn($childUnitPrice);
        $childItem->method('getParentItem')->willReturn($parentItem);
        $childItem->method('getProduct')->willReturn($product);

        $this->model->setAttribute('quote_item_price');
        $this->model->setData('operator', '<');
        $this->model->setValue(50);

        $this->assertFalse(
            $this->model->validate($childItem),
            'Coupon should not apply when parent price is 100 and condition is < 50'
        );
    }

    /**
     * Ensure price comes from the item itself when no parent exists.
     */
    public function testQuoteItemPriceUsesOwnItemPriceWhenNoParent(): void
    {
        $unitPrice = 100.0;

        $attr = $this->createPartialMock(Product::class, ['getAttribute']);
        $attr->method('getAttribute')->willReturn(
            new DataObject(['frontend_input' => 'text', 'backend_type' => 'varchar'])
        );

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResource', 'hasData', 'getData'])
            ->addMethods(['setQuoteItemQty', 'setQuoteItemPrice', 'setQuoteItemRowTotal'])
            ->getMock();
        $product->method('getResource')->willReturn($attr);
        $product->method('hasData')->willReturn(true);
        $product->method('getData')->with('quote_item_price')->willReturn($unitPrice);
        $product->method('setQuoteItemQty')->willReturnSelf();
        $product->expects($this->once())
            ->method('setQuoteItemPrice')
            ->with($this->equalTo($unitPrice))
            ->willReturnSelf();
        $product->method('setQuoteItemRowTotal')->willReturnSelf();

        $item = $this->getMockBuilder(AbstractItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQty', 'getPrice', 'getParentItem', 'getProduct'])
            ->addMethods(['getBaseRowTotal'])
            ->getMockForAbstractClass();
        $item->method('getQty')->willReturn(1);
        $item->method('getPrice')->willReturn($unitPrice);
        $item->method('getBaseRowTotal')->willReturn($unitPrice);
        $item->method('getParentItem')->willReturn(null);
        $item->method('getProduct')->willReturn($product);

        $this->model->setAttribute('quote_item_price');
        $this->model->setData('operator', '<');
        $this->model->setValue(50);

        $this->assertFalse(
            $this->model->validate($item),
            'Coupon should not apply when price is 100 and condition is < 50'
        );
    }

    /**
     * Validates setAttribute parsing of scope and related getters.
     */
    public function testSetAttributeParsesScopeAndGetters(): void
    {
        $this->model->setAttribute('parent::quote_item_qty');
        $this->assertSame('quote_item_qty', $this->model->getAttribute());
        $this->assertSame('parent', $this->model->getAttributeScope());
    }

    /**
     * Ensures getAttributeName resolves label correctly when scope is set.
     */
    public function testGetAttributeNameReturnsSpecialLabelWithScope(): void
    {
        // load options so special attributes are available
        $this->model->loadAttributeOptions();
        $this->model->setAttribute('parent::quote_item_qty');
        $this->assertSame('Quantity in cart', (string)$this->model->getAttributeName());
    }

    /**
     * Ensures attribute_scope is preserved via asArray/loadArray.
     */
    public function testAsArrayAndLoadArrayIncludeAttributeScope(): void
    {
        $this->model->setAttribute('children::category_ids');
        $array = $this->model->asArray();
        $this->assertArrayHasKey('attribute_scope', $array);

        $this->model->loadArray([
            'type' => SalesRuleProduct::class,
            'attribute_scope' => 'parent'
        ]);
        $this->assertSame('parent', $this->model->getAttributeScope());
    }

    /**
     * Confirms special attributes are available after loadAttributeOptions.
     */
    public function testLoadAttributeOptionsAddsSpecialAttributes(): void
    {
        $this->model->loadAttributeOptions();
        $options = $this->model->getAttributeOption();
        $this->assertArrayHasKey('quote_item_price', $options);
        $this->assertArrayHasKey('parent::quote_item_qty', $options);
        $this->assertArrayHasKey('quote_item_row_total', $options);
    }

    /**
     * Ensures missing attribute is set/unset around validation.
     */
    public function testValidateSetsAndUnsetsMissingAttributeOnProduct(): void
    {
        $attrCode = 'nonexistent_attr';
        $this->model->setAttribute($attrCode);
        $this->model->setData('operator', '==');
        $this->model->setValue('x');

        $eavAttr = new DataObject(['frontend_input' => 'text', 'backend_type' => 'varchar']);
        $resource = $this->createPartialMock(Product::class, ['getAttribute']);
        $resource->method('getAttribute')->with($attrCode)->willReturn($eavAttr);

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResource', 'hasData', 'getData', 'setData', 'unsetData'])
            ->addMethods(['setQuoteItemQty', 'setQuoteItemPrice', 'setQuoteItemRowTotal'])
            ->getMock();
        $product->method('getResource')->willReturn($resource);
        $product->method('hasData')->with($attrCode)->willReturnOnConsecutiveCalls(false, true);
        $product->method('getData')->with($attrCode)->willReturn(null);
        $product->expects($this->once())->method('setData')->with($attrCode, null)->willReturnSelf();
        $product->expects($this->once())->method('unsetData')->with($attrCode)->willReturnSelf();
        $product->method('setQuoteItemQty')->willReturnSelf();
        $product->method('setQuoteItemPrice')->willReturnSelf();
        $product->method('setQuoteItemRowTotal')->willReturnSelf();

        $item = $this->getMockBuilder(AbstractItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQty', 'getPrice', 'getParentItem', 'getProduct'])
            ->addMethods(['getBaseRowTotal', 'getProductId'])
            ->getMockForAbstractClass();
        $item->method('getQty')->willReturn(1);
        $item->method('getPrice')->willReturn(10.0);
        $item->method('getBaseRowTotal')->willReturn(10.0);
        $item->method('getParentItem')->willReturn(null);
        $item->method('getProduct')->willReturn($product);
        $item->method('getProductId')->willReturn(1);

        // We only assert that no exceptions occur and our expectations on product are met.
        $this->model->validate($item);
    }

    /**
     * Ensures hidden scope field is appended to attribute element HTML.
     */
    public function testGetAttributeElementHtmlAppendsHiddenScopeField(): void
    {
        // Ensure scope is set to "parent" so it should be passed as hidden field value
        $this->model->setAttribute('parent::quote_item_qty');

        $elementHidden = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\AbstractElement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHtml'])
            ->getMockForAbstractClass();
        $elementHidden->method('getHtml')->willReturn('HIDDEN_HTML');
        $elementSelect = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\AbstractElement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHtml'])
            ->getMockForAbstractClass();
        $elementSelect->method('getHtml')->willReturn('ATTR_HTML');

        $capturedConfig = null;
        $form = $this->getMockBuilder(\Magento\Framework\Data\Form::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addField'])
            ->getMock();
        $form->method('addField')
            ->willReturnCallback(function ($id, $type, $cfg) use (&$capturedConfig, $elementHidden, $elementSelect) {
                if (strpos((string)$id, '__attribute_scope') !== false && $type === 'hidden') {
                    $capturedConfig = $cfg;
                    return $elementHidden;
                }
                return $elementSelect;
            });

        $rule = $this->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getForm'])
            ->getMock();
        $rule->method('getForm')->willReturn($form);
        $this->model->setRule($rule);
        $this->model->setFormName('form-name');
        // Inject a layout so getBlockSingleton() calls succeed
        $layout = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $editable = $this->getMockBuilder(\Magento\Rule\Block\Editable::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layout->method('getBlockSingleton')->willReturn($editable);
        $ref = new \ReflectionProperty(\Magento\Rule\Model\Condition\AbstractCondition::class, '_layout');
        $ref->setAccessible(true);
        $ref->setValue($this->model, $layout);
        $html = $this->model->getAttributeElementHtml();

        $this->assertStringContainsString('HIDDEN_HTML', $html);
        $this->assertIsArray($capturedConfig);
        $this->assertArrayHasKey('value', $capturedConfig);
        $this->assertSame('parent', $capturedConfig['value']);
        $this->assertArrayHasKey('no_span', $capturedConfig);
        $this->assertTrue($capturedConfig['no_span']);
        $this->assertArrayHasKey('class', $capturedConfig);
        $this->assertSame('hidden', $capturedConfig['class']);
    }

    /**
     * Ensures getAttribute strips the scope delimiter.
     */
    public function testGetAttributeStripsScopeDelimiter(): void
    {
        // Simulate legacy/raw storage where attribute includes scope delimiter
        $this->model->setData('attribute', 'parent::category_ids');
        $this->assertSame('category_ids', $this->model->getAttribute());
    }

    /**
     * Ensures getAttribute returns value unchanged without delimiter.
     */
    public function testGetAttributeWithoutDelimiterReturnsAsIs(): void
    {
        $this->model->setData('attribute', 'sku');
        $this->assertSame('sku', $this->model->getAttribute());
    }
}
