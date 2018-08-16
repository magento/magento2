<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Unit\Model\Rule\Condition;

use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\ScopeResolverInterface;
use \Magento\Framework\DB\Adapter\AdapterInterface;
use \Magento\Framework\DB\Select;
use Magento\Framework\Locale\Format;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use \Magento\Rule\Model\Condition\Context;
use \Magento\Backend\Helper\Data;
use \Magento\Eav\Model\Config;
use \Magento\Catalog\Model\ProductFactory;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Eav\Model\Entity\AbstractEntity;
use \Magento\Catalog\Model\ResourceModel\Product;
use \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use \Magento\Framework\Locale\FormatInterface;
use \Magento\Eav\Model\Entity\AttributeLoaderInterface;
use \Magento\SalesRule\Model\Rule\Condition\Product as SalesRuleProduct;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    /** @var SalesRuleProduct */
    protected $model;

    /** @var Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendHelperMock;

    /** @var Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $configMock;

    /** @var ProductFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $productFactoryMock;

    /** @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $productRepositoryMock;

    /** @var Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $productMock;

    /** @var Collection|\PHPUnit_Framework_MockObject_MockObject */
    protected $collectionMock;

    /** @var FormatInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $formatMock;

    /** @var AttributeLoaderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeLoaderInterfaceMock;

    /** @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $adapterInterfaceMock;

    /** @var Select|\PHPUnit_Framework_MockObject_MockObject */
    protected $selectMock;

    /**
     * Setup the test
     */
    protected function setUp()
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
            ->setMethods(['getAttributesByCode'])
            ->getMock();
        $this->attributeLoaderInterfaceMock
            ->expects($this->any())
            ->method('getAttributesByCode')
            ->willReturn([]);
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->setMethods(['distinct', 'from', 'where'])
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
            ->setMethods(['fetchCol', 'select'])
            ->getMockForAbstractClass();
        $this->adapterInterfaceMock
            ->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadAllAttributes', 'getConnection', 'getTable'])
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
        $this->formatMock = new Format(
            $this->getMockBuilder(ScopeResolverInterface::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ResolverInterface::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(CurrencyFactory::class)->disableOriginalConstructor()->getMock()
        );

        $this->model = new SalesRuleProduct(
            $this->contextMock,
            $this->backendHelperMock,
            $this->configMock,
            $this->productFactoryMock,
            $this->productRepositoryMock,
            $this->productMock,
            $this->collectionMock,
            $this->formatMock
        );
    }

    /**
     * @return array
     */
    public function getValueElementChooserUrlDataProvider()
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

    public function testValidateCategoriesIgnoresVisibility()
    {
        /* @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute', 'getId', 'setQuoteItemQty', 'setQuoteItemPrice'])
            ->getMock();
        $product
            ->expects($this->any())
            ->method('setQuoteItemQty')
            ->willReturnSelf();
        $product
            ->expects($this->any())
            ->method('setQuoteItemPrice')
            ->willReturnSelf();
        /* @var AbstractItem|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMockBuilder(AbstractItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMockForAbstractClass();
        $item->expects($this->any())
            ->method('getProduct')
            ->willReturn($product);
        $this->model->setAttribute('category_ids');

        $this->selectMock
            ->expects($this->once())
            ->method('where')
            ->with($this->logicalNot($this->stringContains('visibility')), $this->anything(), $this->anything());

        $this->model->validate($item);
    }

    /**
     * @param boolean $isValid
     * @param string $conditionValue
     * @param string $operator
     * @param double $productPrice
     * @dataProvider localisationProvider
     */
    public function testQuoteLocaleFormatPrice($isValid, $conditionValue, $operator = '>=', $productPrice = 2000.00)
    {
        $attr = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute'])
            ->getMockForAbstractClass();

        $attr->expects($this->any())
            ->method('getAttribute')
            ->willReturn('');

        /* @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['setQuoteItemPrice', 'getResource', 'hasData', 'getData',])
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

        /* @var AbstractItem|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMockBuilder(AbstractItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrice', 'getProduct',])
            ->getMockForAbstractClass();

        $item->expects($this->any())
            ->method('getPrice')
            ->willReturn($productPrice);

        $item->expects($this->any())
            ->method('getProduct')
            ->willReturn($product);

        $this->model->setAttribute('quote_item_price')
            ->setOperator($operator);

        $this->assertEquals($isValid, $this->model->setValue($conditionValue)->validate($item));
    }

    /**
     * DataProvider for testQuoteLocaleFormatPrice
     *
     * @return array
     */
    public function localisationProvider(): array
    {
        return [
            'number' => [true, 500.01],
            'locale' => [true, '1,500.03'],
            'operation' => [true, '1,500.03', '!='],
            'stringOperation' => [false, '1,500.03', '{}'],
            'smallPrice' => [false, '1,500.03', '>=', 1000],
        ];
    }
}
