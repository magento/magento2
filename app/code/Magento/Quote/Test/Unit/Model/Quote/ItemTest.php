<?php
/**
 * Copyright 2012 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Compare;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\Quote\Model\Quote\Item\Option\Comparator;
use Magento\Quote\Model\Quote\Item\OptionFactory;
use Magento\Sales\Model\Status\ListFactory;
use Magento\Sales\Model\Status\ListStatus;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemTest extends TestCase
{
    /**
     * @var Item
     */
    private $model;

    /**
     * @var FormatInterface
     */
    private $localeFormat;

    /**
     * @var Context
     */
    private $modelContext;

    /**
     * @var ManagerInterface
     */
    private $eventDispatcher;

    /**
     * @var ListStatus
     */
    private $errorInfos;

    /**
     * @var OptionFactory
     */
    private $itemOptionFactory;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var Compare|MockObject
     */
    protected $compareHelper;

    /**
     * @var MockObject
     */
    protected $stockItemMock;

    /**
     * @var Json
     */
    private $serializer;

    private const PRODUCT_ID = 1;
    private const PRODUCT_TYPE = 'simple';
    private const PRODUCT_SKU = '12345';
    private const PRODUCT_NAME = 'test_product';
    private const PRODUCT_WEIGHT = '1lb';
    private const PRODUCT_TAX_CLASS_ID = 3;
    private const PRODUCT_COST = 9.00;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);

        $this->localeFormat = $this->getMockBuilder(FormatInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->modelContext = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEventDispatcher'])
            ->getMock();

        $this->eventDispatcher = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['dispatch'])
            ->getMockForAbstractClass();

        $this->modelContext->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($this->eventDispatcher);

        $this->errorInfos = $this->getMockBuilder(ListStatus::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['clear', 'addItem', 'getItems', 'removeItemsByParams'])
            ->getMock();

        $statusListFactory = $this->getMockBuilder(ListFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $statusListFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->errorInfos);

        $this->itemOptionFactory = $this->getMockBuilder(OptionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->compareHelper = $this->createMock(Compare::class);

        $this->stockItemMock = $this->createPartialMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            ['getIsQtyDecimal', '__wakeup']
        );

        $this->serializer = $this->getMockBuilder(Json::class)
            ->onlyMethods(['unserialize'])
            ->getMockForAbstractClass();

        $this->model = $this->objectManagerHelper->getObject(
            Item::class,
            [
                'localeFormat' => $this->localeFormat,
                'context' => $this->modelContext,
                'statusListFactory' => $statusListFactory,
                'itemOptionFactory' => $this->itemOptionFactory,
                'quoteItemCompare' => $this->compareHelper,
                'serializer' => $this->serializer,
                'itemOptionComparator' => new Comparator()
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetAddress(): void
    {
        $quote = $this->getMockBuilder(Quote::class)
            ->onlyMethods(['getShippingAddress', 'getBillingAddress', 'getStoreId', '__wakeup', 'isVirtual'])
            ->disableOriginalConstructor()
            ->getMock();
        $quote->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn('shipping');
        $quote->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn('billing');
        $quote->expects($this->any())
            ->method('getStoreId')
            ->willReturn(1);
        $quote->expects($this->exactly(2))
            ->method('isVirtual')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->model->setQuote($quote);
        $this->assertEquals('shipping', $this->model->getAddress(), 'Wrong shipping address');
        $this->assertEquals('billing', $this->model->getAddress(), 'Wrong billing address');
    }

    /**
     * @return void
     */
    public function testSetAndQuote(): void
    {
        $idValue = "id_value";

        $quote = $this->getMockBuilder(Quote::class)
            ->onlyMethods(['getId', 'getStoreId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $quote->expects($this->once())
            ->method('getId')
            ->willReturn($idValue);
        $quote->expects($this->any())
            ->method('getStoreId')
            ->willReturn(1);

        $this->model->setQuote($quote);

        $this->assertSame($quote, $this->model->getQuote());
        $this->assertEquals($idValue, $this->model->getQuoteId());
    }

    /**
     * Tests that adding a quantity to an item without a parent item or an id will add additional quantity.
     *
     * @return void
     */
    public function testAddQtyNormal(): void
    {
        $existingQuantity = 2;
        $quantityToAdd = 3;
        $preparedQuantityToAdd = 4;

        $this->model->setData('qty', $existingQuantity);

        $this->localeFormat
            ->method('getNumber')
            ->willReturnCallback(
                function ($arg1) use ($quantityToAdd, $existingQuantity, $preparedQuantityToAdd) {
                    if ($arg1 == $quantityToAdd) {
                        return $preparedQuantityToAdd;
                    } elseif ($arg1 == $preparedQuantityToAdd + $existingQuantity) {
                        return $preparedQuantityToAdd + $existingQuantity;
                    }
                }
            );

        $this->model->addQty($quantityToAdd);
        $this->assertEquals($preparedQuantityToAdd, $this->model->getQtyToAdd());
        $this->assertEquals($preparedQuantityToAdd + $existingQuantity, $this->model->getQty());
    }

    /**
     * Tests that adding a quantity to an item with a parent item and an id will not change the quantity.
     *
     * @return void
     */
    public function testAddQtyExistingParentItemAndId(): void
    {
        $existingQuantity = 2;
        $quantityToAdd = 3;

        $parentItemMock = $this->getMockBuilder(Item::class)
            ->onlyMethods(['addChild', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model->setParentItem($parentItemMock);
        $this->model->setId(1);
        $this->model->setData('qty', $existingQuantity);

        $this->model->addQty($quantityToAdd);
        $this->assertEquals($existingQuantity, $this->model->getQty());
        $this->assertNull($this->model->getQtyToAdd());
    }

    /**
     * @return void
     */
    public function testSetQty(): void
    {
        $existingQuantity = 2;
        $quantityToAdd = 3;
        $preparedQuantityToAdd = 4;

        $this->localeFormat->expects($this->once())
            ->method('getNumber')
            ->with($quantityToAdd)
            ->willReturn($preparedQuantityToAdd);

        $this->model->setData('qty', $existingQuantity);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with('sales_quote_item_qty_set_after', ['item' => $this->model]);

        $this->model->setQty($quantityToAdd);
        $this->assertEquals($preparedQuantityToAdd, $this->model->getQty());
    }

    /**
     * @return void
     */
    public function testSetQtyQuoteIgnoreOldQuantity(): void
    {
        $existingQuantity = 2;
        $quantityToAdd = 3;
        $preparedQuantityToAdd = 4;

        $this->localeFormat->expects($this->once())
            ->method('getNumber')
            ->with($quantityToAdd)
            ->willReturn($preparedQuantityToAdd);

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStoreId', '__wakeup'])
            ->addMethods(['getIgnoreOldQty'])
            ->getMock();
        $quoteMock->expects($this->once())
            ->method('getIgnoreOldQty')
            ->willReturn(true);
        $quoteMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn(1);

        $this->model->setQuote($quoteMock);

        $this->model->setData('qty', $existingQuantity);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with('sales_quote_item_qty_set_after', ['item' => $this->model]);

        $this->model->setQty($quantityToAdd);
        $this->assertEquals($preparedQuantityToAdd, $this->model->getQty());
    }

    /**
     * @return void
     */
    public function testSetQtyUseOldQuantity(): void
    {
        $existingQuantity = 2;
        $quantityToAdd = 3;
        $preparedQuantityToAdd = 4;

        $this->localeFormat->expects($this->once())
            ->method('getNumber')
            ->with($quantityToAdd)
            ->willReturn($preparedQuantityToAdd);

        $this->model->setData('qty', $existingQuantity);
        $this->model->setUseOldQty(true);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with('sales_quote_item_qty_set_after', ['item' => $this->model]);

        $this->model->setQty($quantityToAdd);
        $this->assertEquals($existingQuantity, $this->model->getQty());
    }

    /**
     * @return void
     */
    public function testSetQtyOptions(): void
    {
        $value = ['a' => 'b'];
        $this->model->setQtyOptions($value);
        $this->assertEquals($value, $this->model->getQtyOptions());
    }

    /**
     * @return void
     */
    public function testSetProduct(): void
    {
        $productMock = $this->generateProductMock(
            self::PRODUCT_ID,
            self::PRODUCT_TYPE,
            self::PRODUCT_SKU,
            self::PRODUCT_NAME,
            self::PRODUCT_WEIGHT,
            self::PRODUCT_TAX_CLASS_ID,
            self::PRODUCT_COST
        );

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with('sales_quote_item_set_product', ['product' => $productMock, 'quote_item' => $this->model]);

        $this->model->setProduct($productMock);

        $this->assertEquals($productMock, $this->model->getProduct());
        $this->assertEquals(self::PRODUCT_ID, $this->model->getProductId());
        $this->assertEquals(self::PRODUCT_TYPE, $this->model->getData('product_type'));
        $this->assertEquals(self::PRODUCT_SKU, $this->model->getSku());
        $this->assertEquals(self::PRODUCT_NAME, $this->model->getName());
        $this->assertEquals(self::PRODUCT_WEIGHT, $this->model->getWeight());
        $this->assertEquals(self::PRODUCT_TAX_CLASS_ID, $this->model->getTaxClassId());
        $this->assertEquals(self::PRODUCT_COST, $this->model->getBaseCost());
        $this->assertNull($this->model->getIsQtyDecimal());
    }

    /**
     * @return void
     */
    public function testSetProductWithQuoteAndStockItem(): void
    {
        $productMock = $this->generateProductMock(
            self::PRODUCT_ID,
            self::PRODUCT_TYPE,
            self::PRODUCT_SKU,
            self::PRODUCT_NAME,
            self::PRODUCT_WEIGHT,
            self::PRODUCT_TAX_CLASS_ID,
            self::PRODUCT_COST
        );

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with('sales_quote_item_set_product', ['product' => $productMock, 'quote_item' => $this->model]);

        $isQtyDecimal = true;
        $this->stockItemMock->expects($this->once())
            ->method('getIsQtyDecimal')
            ->willReturn($isQtyDecimal);

        $storeId = 15;
        $customerGroupId = 11;
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStoreId', 'getCustomerGroupId', '__wakeup'])
            ->getMock();
        $quoteMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);
        $quoteMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->willReturn($customerGroupId);
        $this->model->setQuote($quoteMock);

        $productMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $productMock->expects($this->once())
            ->method('setCustomerGroupId')
            ->with($customerGroupId);

        $this->model->setProduct($productMock);

        $this->assertEquals($productMock, $this->model->getProduct());
        $this->assertEquals(self::PRODUCT_ID, $this->model->getProductId());
        $this->assertEquals(self::PRODUCT_TYPE, $this->model->getRealProductType());
        $this->assertEquals(self::PRODUCT_SKU, $this->model->getSku());
        $this->assertEquals(self::PRODUCT_NAME, $this->model->getName());
        $this->assertEquals(self::PRODUCT_WEIGHT, $this->model->getWeight());
        $this->assertEquals(self::PRODUCT_TAX_CLASS_ID, $this->model->getTaxClassId());
        $this->assertEquals(self::PRODUCT_COST, $this->model->getBaseCost());
        $this->assertEquals($isQtyDecimal, $this->model->getIsQtyDecimal());
    }

    /**
     * Generate product mock.
     *
     * @param int $productId
     * @param string $productType
     * @param string $productSku
     * @param string $productName
     * @param string $productWeight
     * @param int $productTaxClassId
     * @param float $productCost
     *
     * @return MockObject
     */
    private function generateProductMock(
        int $productId,
        string $productType,
        string $productSku,
        string $productName,
        string $productWeight,
        int $productTaxClassId,
        float $productCost
    ): MockObject {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getId',
                    'getTypeId',
                    'getSku',
                    'getName',
                    'getWeight',
                    'setStoreId',
                    'getTypeInstance',
                    'getCustomOptions',
                    'getExtensionAttributes',
                    'toArray',
                    '__wakeup',
                    'getStore'
                ]
            )
            ->addMethods(
                [
                    'getTaxClassId',
                    'getCost',
                    'setCustomerGroupId',
                    'getStickWithinParent'
                ]
            )
            ->getMock();

        $productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);
        $productMock->expects($this->any())
            ->method('getTypeId')
            ->willReturn($productType);
        $productMock->expects($this->any())
            ->method('getSku')
            ->willReturn($productSku);
        $productMock->expects($this->any())
            ->method('getName')
            ->willReturn($productName);
        $productMock->expects($this->any())
            ->method('getWeight')
            ->willReturn($productWeight);
        $productMock->expects($this->any())
            ->method('getTaxClassId')
            ->willReturn($productTaxClassId);
        $productMock->expects($this->any())
            ->method('getCost')
            ->willReturn($productCost);
        $store = $this->createPartialMock(Store::class, ['getWebsiteId']);
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(10);

        $productMock->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
        $extensionAttribute = $this->getMockBuilder(ProductExtensionInterface::class)
            ->addMethods(['getStockItem'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttribute->expects($this->atLeastOnce())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $productMock->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttribute);
        return $productMock;
    }

    /**
     * @return void
     */
    public function testRepresentProductNoProduct(): void
    {
        $productMock = $this->generateProductMock(
            self::PRODUCT_ID,
            self::PRODUCT_TYPE,
            self::PRODUCT_SKU,
            self::PRODUCT_NAME,
            self::PRODUCT_WEIGHT,
            self::PRODUCT_TAX_CLASS_ID,
            self::PRODUCT_COST
        );

        $this->model->setProduct($productMock);

        $this->assertFalse($this->model->representProduct(null));
    }

    /**
     * @return void
     */
    public function testRepresentProductStickWithinParentNotSameAsParentItem(): void
    {
        $productMock = $this->generateProductMock(
            self::PRODUCT_ID,
            self::PRODUCT_TYPE,
            self::PRODUCT_SKU,
            self::PRODUCT_NAME,
            self::PRODUCT_WEIGHT,
            self::PRODUCT_TAX_CLASS_ID,
            self::PRODUCT_COST
        );

        $parentItemMock = $this->getMockBuilder(Item::class)
            ->onlyMethods(['addChild', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model->setProduct($productMock);
        $this->model->setParentItem($parentItemMock);

        $productMock->expects($this->once())
            ->method('getStickWithinParent')
            ->willReturn(true);

        $this->assertFalse($this->model->representProduct($productMock));
    }

    /**
     * @return void
     */
    public function testRepresentProductItemOptionsNotInProductOptions(): void
    {
        $productMock = $this->generateProductMock(
            self::PRODUCT_ID,
            self::PRODUCT_TYPE,
            self::PRODUCT_SKU,
            self::PRODUCT_NAME,
            self::PRODUCT_WEIGHT,
            self::PRODUCT_TAX_CLASS_ID,
            self::PRODUCT_COST
        );

        $parentItemMock = $this->getMockBuilder(Item::class)
            ->onlyMethods(['addChild', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model->setProduct($productMock);
        $this->model->setParentItem($parentItemMock);

        $optionCode1 = 1234;
        $optionMock1 = $this->createOptionMock($optionCode1);
        $optionMock1->expects($this->any())
            ->method('getValue')
            ->willReturn(1234);

        $optionCode2 = 7890;
        $optionMock2 = $this->createOptionMock($optionCode2);
        $optionMock2->expects($this->any())
            ->method('getValue')
            ->willReturn(7890);
        $this->model->setOptions([$optionMock1, $optionMock2]);

        $productMock->expects($this->once())
            ->method('getStickWithinParent')
            ->willReturn($parentItemMock);
        $productMock->expects($this->once())
            ->method('getCustomOptions')
            ->willReturn([$optionCode1 => $optionMock1]);

        $this->assertFalse($this->model->representProduct($productMock));
    }

    /**
     * @return void
     */
    public function testRepresentProductProductOptionsNotInItemOptions(): void
    {
        $productMock = $this->generateProductMock(
            self::PRODUCT_ID,
            self::PRODUCT_TYPE,
            self::PRODUCT_SKU,
            self::PRODUCT_NAME,
            self::PRODUCT_WEIGHT,
            self::PRODUCT_TAX_CLASS_ID,
            self::PRODUCT_COST
        );

        $parentItemMock = $this->getMockBuilder(Item::class)
            ->onlyMethods(['addChild', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model->setProduct($productMock);
        $this->model->setParentItem($parentItemMock);

        $optionCode1 = 1234;
        $optionMock1 = $this->createOptionMock($optionCode1);
        $optionMock1->expects($this->any())
            ->method('getValue')
            ->willReturn(1234);

        $optionCode2 = 7890;
        $optionMock2 = $this->createOptionMock($optionCode2);
        $optionMock2->expects($this->any())
            ->method('getValue')
            ->willReturn(7890);
        $this->model->setOptions([$optionMock1]);

        $productMock->expects($this->once())
            ->method('getStickWithinParent')
            ->willReturn($parentItemMock);
        $productMock->expects($this->once())
            ->method('getCustomOptions')
            ->willReturn([$optionCode1 => $optionMock1, $optionCode2 => $optionMock2]);

        $this->assertFalse($this->model->representProduct($productMock));
    }

    /**
     * @return void
     */
    public function testRepresentProductTrue(): void
    {
        $productMock = $this->generateProductMock(
            self::PRODUCT_ID,
            self::PRODUCT_TYPE,
            self::PRODUCT_SKU,
            self::PRODUCT_NAME,
            self::PRODUCT_WEIGHT,
            self::PRODUCT_TAX_CLASS_ID,
            self::PRODUCT_COST
        );

        $parentItemMock = $this->getMockBuilder(Item::class)
            ->onlyMethods(['addChild', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model->setProduct($productMock);
        $this->model->setParentItem($parentItemMock);

        $optionCode1 = 1234;
        $optionMock1 = $this->createOptionMock($optionCode1);
        $optionMock1->expects($this->any())
            ->method('getValue')
            ->willReturn(1234);

        $optionCode2 = 7890;
        $optionMock2 = $this->createOptionMock($optionCode2);
        $optionMock2->expects($this->any())
            ->method('getValue')
            ->willReturn(7890);
        $this->model->setOptions([$optionMock1, $optionMock2]);

        $productMock->expects($this->once())
            ->method('getStickWithinParent')
            ->willReturn($parentItemMock);
        $productMock->expects($this->once())
            ->method('getCustomOptions')
            ->willReturn([$optionCode1 => $optionMock1, $optionCode2 => $optionMock2]);

        $this->assertTrue($this->model->representProduct($productMock));
    }

    /**
     * test compare
     *
     * @return void
     */
    public function testCompare(): void
    {
        $itemMock = $this->createMock(Item::class);
        $this->compareHelper->expects($this->once())
            ->method('compare')
            ->with($this->model, $itemMock)
            ->willReturn(true);
        $this->assertTrue($this->model->compare($itemMock));
    }

    /**
     * @return void
     */
    public function testCompareOptionsEqual(): void
    {
        $optionCode1 = 1234;
        $optionMock1 = $this->createOptionMock($optionCode1);
        $optionMock1->expects($this->any())
            ->method('getValue')
            ->willReturn(1234);

        $this->assertTrue(
            $this->model->compareOptions([$optionCode1 => $optionMock1], [$optionCode1 => $optionMock1])
        );
    }

    /**
     * @return void
     */
    public function testCompareOptionsDifferentValues(): void
    {
        $optionCode1 = 1234;
        $optionMock1 = $this->createOptionMock($optionCode1);
        $optionMock1->expects($this->any())
            ->method('getValue')
            ->willReturn(1234);

        $optionCode2 = 1234;
        $optionMock2 = $this->createOptionMock($optionCode1);
        $optionMock2->expects($this->any())
            ->method('getValue')
            ->willReturn(7890);

        $this->assertFalse(
            $this->model->compareOptions([$optionCode1 => $optionMock1], [$optionCode2 => $optionMock2])
        );
    }

    /**
     * @return void
     */
    public function testCompareOptionsNullValues(): void
    {
        $optionCode1 = 1234;
        $optionMock1 = $this->createOptionMock($optionCode1);
        $optionMock1->expects($this->any())
            ->method('getValue')
            ->willReturn(1234);

        $optionCode2 = 1234;
        $optionMock2 = $this->createOptionMock($optionCode1);
        $optionMock2->expects($this->any())
            ->method('getValue')
            ->willReturn(null);

        $this->assertFalse(
            $this->model->compareOptions([$optionCode1 => $optionMock1], [$optionCode2 => $optionMock2])
        );
    }

    /**
     * @return void
     */
    public function testCompareOptionsMultipleEquals(): void
    {
        $optionCode1 = 1234;
        $optionMock1 = $this->createOptionMock($optionCode1);
        $optionMock1->expects($this->any())
            ->method('getValue')
            ->willReturn(1234);

        $optionCode2 = 7890;
        $optionMock2 = $this->createOptionMock($optionCode1);
        $optionMock2->expects($this->any())
            ->method('getValue')
            ->willReturn(7890);

        $this->assertFalse(
            $this->model->compareOptions(
                [$optionCode1 => $optionMock1, $optionCode2 => $optionMock2],
                [$optionCode1 => $optionMock1, $optionCode2 => $optionMock2]
            )
        );
    }

    /**
     * @return void
     */
    public function testGetQtyOptions(): void
    {
        $optionCode1 = 1234;
        $optionMock1 = $this->createOptionMock($optionCode1);
        $productMock = $this->generateProductMock(
            self::PRODUCT_ID,
            self::PRODUCT_TYPE,
            self::PRODUCT_SKU,
            self::PRODUCT_NAME,
            self::PRODUCT_WEIGHT,
            self::PRODUCT_TAX_CLASS_ID,
            self::PRODUCT_COST
        );
        $optionMock1->expects($this->any())
            ->method('getProduct')
            ->willReturn($productMock);

        $optionCode2 = 'product_qty_' . self::PRODUCT_ID;
        $optionMock2 = $this->createOptionMock($optionCode2);

        $productMock2 = $this->generateProductMock(
            self::PRODUCT_ID + 1,
            self::PRODUCT_TYPE,
            self::PRODUCT_SKU,
            self::PRODUCT_NAME,
            self::PRODUCT_WEIGHT,
            self::PRODUCT_TAX_CLASS_ID,
            self::PRODUCT_COST
        );

        $this->model->setProduct($productMock);
        $this->model->setProduct($productMock2);
        $this->model->setOptions([$optionCode1 => $optionMock1, $optionCode2 => $optionMock2]);

        $this->assertEquals([self::PRODUCT_ID => $optionMock2], $this->model->getQtyOptions());
    }

    /**
     * @return void
     */
    public function testToArray(): void
    {
        $productMock = $this->generateProductMock(
            self::PRODUCT_ID,
            self::PRODUCT_TYPE,
            self::PRODUCT_SKU,
            self::PRODUCT_NAME,
            self::PRODUCT_WEIGHT,
            self::PRODUCT_TAX_CLASS_ID,
            self::PRODUCT_COST
        );

        $this->model->setProduct($productMock);

        $toArrayValue = ['a' => 'b'];
        $productMock->expects($this->once())
            ->method('toArray')
            ->willReturn($toArrayValue);

        $data = $this->model->toArray();
        $this->assertEquals($toArrayValue, $data['product']);
    }

    /**
     * @return void
     */
    public function testGetProductTypeOption(): void
    {
        $optionProductType = 'product_type';
        $optionProductTypeValue = 'abcd';
        $optionMock = $this->createOptionMock($optionProductType);
        $optionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($optionProductTypeValue);
        $this->model->addOption($optionMock);

        $this->assertEquals($optionProductTypeValue, $this->model->getProductType());
    }

    /**
     * @return void
     */
    public function testGetProductTypeWithProduct(): void
    {
        $productMock = $this->generateProductMock(
            self::PRODUCT_ID,
            self::PRODUCT_TYPE,
            self::PRODUCT_SKU,
            self::PRODUCT_NAME,
            self::PRODUCT_WEIGHT,
            self::PRODUCT_TAX_CLASS_ID,
            self::PRODUCT_COST
        );
        $this->model->setProduct($productMock);
        $this->assertEquals(self::PRODUCT_TYPE, $this->model->getProductType());
    }

    /**
     * @return void
     */
    public function testSetOptions(): void
    {
        $optionCode1 = 1234;
        $optionMock1 = $this->createOptionMock($optionCode1);

        $optionCode2 = 7890;
        $optionMock2 = $this->createOptionMock($optionCode2);

        $this->assertSame($this->model, $this->model->setOptions([$optionMock1, $optionMock2]));
        $this->assertEquals([$optionMock1, $optionMock2], $this->model->getOptions());
        $this->assertEquals($optionMock1, $this->model->getOptionByCode($optionCode1));
        $this->assertEquals($optionMock2, $this->model->getOptionByCode($optionCode2));
    }

    /**
     * @return void
     */
    public function testSetOptionsWithNull(): void
    {
        $this->assertEquals($this->model, $this->model->setOptions(null));
    }

    /**
     * @param mixed $optionCode
     * @param array $optionData
     *
     * @return MockObject
     */
    private function createOptionMock($optionCode, array $optionData = []): MockObject
    {
        $optionMock = $this->getMockBuilder(Option::class)
            ->onlyMethods(
                [
                    'setData',
                    'setItem',
                    'getItem',
                    '__wakeup',
                    'isDeleted',
                    'delete',
                    'getValue',
                    'getProduct',
                    'save'
                ]
            )
            ->addMethods(['getCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock->expects($this->any())
            ->method('setData')
            ->with($optionData)
            ->willReturn($optionMock);
        $optionMock->expects($this->any())
            ->method('setItem')
            ->with($this->model)
            ->willReturn($optionMock);
        $optionMock->expects($this->any())
            ->method('getCode')
            ->willReturn($optionCode);

        return $optionMock;
    }

    /**
     * @return void
     */
    public function testAddOptionArray(): void
    {
        $optionCode = 1234;
        $optionData = ['product' => 'test', 'code' => $optionCode];

        $optionMock = $this->getMockBuilder(Option::class)
            ->onlyMethods(['setData', 'setItem', '__wakeup', 'isDeleted'])
            ->addMethods(['getCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock->expects($this->once())
            ->method('setData')
            ->with($optionData)
            ->willReturn($optionMock);
        $optionMock->expects($this->once())
            ->method('setItem')
            ->with($this->model)
            ->willReturn($optionMock);
        $optionMock->expects($this->exactly(3))
            ->method('getCode')
            ->willReturn($optionCode);

        $this->itemOptionFactory
            ->method('create')
            ->willReturn($optionMock);

        $this->model->addOption($optionData);
        $this->assertEquals([$optionMock], $this->model->getOptions());
        $this->assertEquals([$optionCode => $optionMock], $this->model->getOptionsByCode());
        $this->assertEquals($optionMock, $this->model->getOptionByCode($optionCode));
    }

    /**
     * @return void
     */
    public function testUpdateQtyOption(): void
    {
        $productMock = $this->generateProductMock(
            self::PRODUCT_ID,
            self::PRODUCT_TYPE,
            self::PRODUCT_SKU,
            self::PRODUCT_NAME,
            self::PRODUCT_WEIGHT,
            self::PRODUCT_TAX_CLASS_ID,
            self::PRODUCT_COST
        );

        $typeInstanceMock = $this->getMockForAbstractClass(
            AbstractType::class,
            [],
            '',
            false,
            false,
            true,
            ['updateQtyOption']
        );
        $productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($typeInstanceMock);

        $optionMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->addMethods(['getProduct'])
            ->getMock();
        $optionMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);

        $quantityValue = 12;

        $this->model->setProduct($productMock);
        $typeInstanceMock->expects($this->once())
            ->method('updateQtyOption')
            ->with($this->model->getOptions(), $optionMock, $quantityValue, $productMock);
        $this->assertEquals($this->model, $this->model->updateQtyOption($optionMock, $quantityValue));
    }

    /**
     * @return void
     */
    public function testRemoveOption(): void
    {
        $optionCode = 1234;

        $optionMock = $this->getMockBuilder(Option::class)
            ->onlyMethods(['setItem', '__wakeup', 'isDeleted'])
            ->addMethods(['getCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock->expects($this->once())
            ->method('setItem')
            ->with($this->model)
            ->willReturn($optionMock);
        $optionMock->expects($this->exactly(3))
            ->method('getCode')
            ->willReturn($optionCode);
        $optionMock->method('isDeleted')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->model->addOption($optionMock);

        $this->assertEquals($this->model, $this->model->removeOption($optionCode));
    }

    /**
     * @return void
     */
    public function testRemoveOptionNoOptionCodeExists(): void
    {
        $this->assertEquals($this->model, $this->model->removeOption('random'));
    }

    /**
     * @return void
     */
    public function testGetOptionByCodeNonExistent(): void
    {
        $this->assertNull($this->model->getOptionByCode('random'));
    }

    /**
     * @return void
     */
    public function testGetOptionByCodeDeletedCode(): void
    {
        $optionCode = 1234;

        $optionMock = $this->getMockBuilder(Option::class)
            ->onlyMethods(['setItem', '__wakeup', 'isDeleted'])
            ->addMethods(['getCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock->expects($this->once())
            ->method('setItem')
            ->with($this->model)
            ->willReturn($optionMock);
        $optionMock->expects($this->exactly(3))
            ->method('getCode')
            ->willReturn($optionCode);
        $optionMock->expects($this->once())
            ->method('isDeleted')
            ->willReturn(true);

        $this->model->addOption($optionMock);

        $this->assertNull($this->model->getOptionByCode($optionCode));
    }

    /**
     * @return void
     */
    public function testGetOptionByCodeNotDeletedCode(): void
    {
        $optionCode = 1234;

        $optionMock = $this->getMockBuilder(Option::class)
            ->onlyMethods(['setItem', '__wakeup', 'isDeleted'])
            ->addMethods(['getCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock->expects($this->once())
            ->method('setItem')
            ->with($this->model)
            ->willReturn($optionMock);
        $optionMock->expects($this->exactly(3))
            ->method('getCode')
            ->willReturn($optionCode);
        $optionMock->expects($this->once())
            ->method('isDeleted')
            ->willReturn(false);

        $this->model->addOption($optionMock);

        $this->assertSame($optionMock, $this->model->getOptionByCode($optionCode));
    }

    /**
     * @return void
     */
    public function testGetBuyRequestNoOptionByCode(): void
    {
        $quantity = 12;
        $this->localeFormat
            ->method('getNumber')
            ->with($quantity)
            ->willReturn($quantity);
        $this->model->setQty($quantity);
        $this->assertEquals($quantity, $this->model->getQty());
        $buyRequest = $this->model->getBuyRequest();
        $this->assertEquals(0, $buyRequest->getOriginalQty());
        $this->assertEquals($quantity, $buyRequest->getQty());
    }

    /**
     * @return void
     */
    public function testGetBuyRequestOptionByCode(): void
    {
        $optionCode = 'info_buyRequest';
        $buyRequestQuantity = 23;
        $optionMock = $this->getMockBuilder(Option::class)
            ->onlyMethods(['setItem', '__wakeup', 'getValue'])
            ->addMethods(['getCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock->expects($this->once())
            ->method('setItem')
            ->with($this->model)
            ->willReturn($optionMock);
        $optionMock->expects($this->exactly(3))
            ->method('getCode')
            ->willReturn($optionCode);
        $optionMock->expects($this->any())
            ->method('getValue')
            ->willReturn('{"qty":23}');

        $this->model->addOption($optionMock);

        $quantity = 12;
        $this->localeFormat
            ->method('getNumber')
            ->with($quantity)
            ->willReturn($quantity);
        $this->model->setQty($quantity);
        $this->assertEquals($quantity, $this->model->getQty());
        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturn(json_decode($optionMock->getValue(), true));
        $buyRequest = $this->model->getBuyRequest();
        $this->assertEquals($buyRequestQuantity, $buyRequest->getOriginalQty());
        $this->assertEquals($quantity, $buyRequest->getQty());
    }

    /**
     * @return void
     */
    public function testSetHasErrorFalse(): void
    {
        $this->errorInfos->expects($this->once())
            ->method('clear');

        $this->assertEquals($this->model, $this->model->setHasError(false));

        $this->assertFalse($this->model->getHasError());
    }

    /**
     * @return void
     */
    public function testSetHasErrorTrue(): void
    {
        $this->errorInfos->expects($this->once())
            ->method('addItem')
            ->with(null, null, null, null);

        $this->assertEquals($this->model, $this->model->setHasError(true));

        $this->assertTrue($this->model->getHasError());
        $this->assertEquals('', $this->model->getMessage());
    }

    /**
     * @return void
     */
    public function testAddErrorInfo(): void
    {
        $origin = 'origin';
        $code = 1;
        $message = 'message';
        $additionalData = new DataObject();
        $additionalData->setTemp(true);

        $this->errorInfos->expects($this->once())
            ->method('addItem')
            ->with($origin, $code, $message, $additionalData);

        $this->assertEquals($this->model, $this->model->addErrorInfo($origin, $code, $message, $additionalData));

        $this->assertTrue($this->model->getHasError());
        $this->assertEquals($message, $this->model->getMessage());
    }

    /**
     * @return void
     */
    public function testGetErrorInfos(): void
    {
        $retValue = 'return value';

        $this->errorInfos->expects($this->once())
            ->method('getItems')
            ->willReturn($retValue);

        $this->assertEquals($retValue, $this->model->getErrorInfos());
    }

    /**
     * @return void
     */
    public function testRemoveErrorInfosByParams(): void
    {
        $message = 'message';
        $message2 = 'message2';

        $this->errorInfos->method('addItem')
            ->willReturnCallback(
                function ($arg1, $arg2, $arg3) use ($message, $message2) {
                    if ($arg1 == null && $arg2 == null && $arg3 == $message) {
                        return null;
                    } elseif ($arg1 == null && $arg2 == null && $arg3 == $message2) {
                        return null;
                    }
                }
            );
        $this->assertEquals($this->model, $this->model->addErrorInfo(null, null, $message));
        $this->assertEquals($this->model, $this->model->addErrorInfo(null, null, $message2));
        $this->assertEquals($message . "\n" . $message2, $this->model->getMessage());

        $params = [];
        $removedItems = [['message' => $message]];

        $this->errorInfos->expects($this->once())
            ->method('removeItemsByParams')
            ->with($params)
            ->willReturn($removedItems);

        $this->errorInfos->expects($this->once())
            ->method('getItems')
            ->willReturn(true);

        $this->assertEquals($this->model, $this->model->removeErrorInfosByParams($params));
        $this->assertEquals($message2, $this->model->getMessage());
    }

    /**
     * @return void
     */
    public function testRemoveErrorInfosByParamsAllErrorsRemoved(): void
    {
        $message = 'message';
        $message2 = 'message2';

        $this->errorInfos->method('addItem')
            ->willReturnCallback(
                function ($arg1, $arg2, $arg3) use ($message, $message2) {
                    if ($arg1 == null && $arg2 == null && $arg3 == $message) {
                        return null;
                    } elseif ($arg1 == null && $arg2 == null && $arg3 == $message2) {
                        return null;
                    }
                }
            );
        $this->assertEquals($this->model, $this->model->addErrorInfo(null, null, $message));
        $this->assertEquals($this->model, $this->model->addErrorInfo(null, null, $message2));
        $this->assertEquals($message . "\n" . $message2, $this->model->getMessage());

        $params = [];
        $removedItems = [['message' => $message], ['message' => $message2]];

        $this->errorInfos->expects($this->once())
            ->method('removeItemsByParams')
            ->with($params)
            ->willReturn($removedItems);

        $this->errorInfos->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $this->assertEquals($this->model, $this->model->removeErrorInfosByParams($params));
        $this->assertFalse($this->model->getHasError());
        $this->assertEquals('', $this->model->getMessage());
    }

    /**
     * Test method \Magento\Quote\Model\Quote\Item::saveItemOptions
     *
     * @return void
     */
    public function testSaveItemOptions(): void
    {
        $optionMockDeleted = $this->createOptionMock(100);
        $optionMockDeleted->expects(self::once())->method('isDeleted')->willReturn(true);
        $optionMockDeleted->expects(self::once())->method('delete');

        $optionMock1 = $this->createOptionMock(200);
        $optionMock1->expects(self::once())->method('isDeleted')->willReturn(false);
        $quoteItemMock1 = $this->createPartialMock(Item::class, ['getId']);
        $quoteItemMock1->expects(self::once())->method('getId')->willReturn(null);
        $optionMock1->expects(self::exactly(2))->method('getItem')->willReturn($quoteItemMock1);
        $optionMock1->expects(self::exactly(2))->method('setItem')->with($this->model);
        $optionMock1->expects(self::once())->method('save');

        $optionMock2 = $this->createOptionMock(300);
        $optionMock2->expects(self::once())->method('isDeleted')->willReturn(false);
        $quoteItemMock2 = $this->createPartialMock(Item::class, ['getId']);
        $quoteItemMock2->expects(self::once())->method('getId')->willReturn(11);
        $optionMock2->expects(self::exactly(2))->method('getItem')->willReturn($quoteItemMock2);
        $optionMock2->expects(self::once())->method('setItem')->with($this->model);
        $optionMock2->expects(self::once())->method('save');

        $this->model->setOptions([$optionMockDeleted, $optionMock1, $optionMock2]);
        $this->model->saveItemOptions();
    }
}
