<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\Quote;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    private $model;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $localeFormat;

    /**
     * @var \Magento\Framework\Model\Context
     */
    private $modelContext;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventDispatcher;

    /**
     * @var \Magento\Sales\Model\Status\ListStatus
     */
    private $errorInfos;

    /**
     * @var \Magento\Quote\Model\Quote\Item\OptionFactory
     */
    private $itemOptionFactory;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Quote\Model\Quote\Item\Compare|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $compareHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemMock;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    const PRODUCT_ID = 1;
    const PRODUCT_TYPE = 'simple';
    const PRODUCT_SKU = '12345';
    const PRODUCT_NAME = 'test_product';
    const PRODUCT_WEIGHT = '1lb';
    const PRODUCT_TAX_CLASS_ID = 3;
    const PRODUCT_COST = '9.00';

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->localeFormat = $this->getMockBuilder(\Magento\Framework\Locale\FormatInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->modelContext = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEventDispatcher'])
            ->getMock();

        $this->eventDispatcher = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMock();

        $this->modelContext->expects($this->any())
            ->method('getEventDispatcher')
            ->will($this->returnValue($this->eventDispatcher));

        $this->errorInfos = $this->getMockBuilder(\Magento\Sales\Model\Status\ListStatus::class)
            ->disableOriginalConstructor()
            ->setMethods(['clear', 'addItem', 'getItems', 'removeItemsByParams'])
            ->getMock();

        $statusListFactory = $this->getMockBuilder(\Magento\Sales\Model\Status\ListFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $statusListFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->errorInfos));

        $this->itemOptionFactory = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\OptionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->compareHelper = $this->createMock(\Magento\Quote\Model\Quote\Item\Compare::class);

        $this->stockItemMock = $this->createPartialMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            ['getIsQtyDecimal', '__wakeup']
        );

        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->setMethods(['unserialize'])
            ->getMockForAbstractClass();

        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Quote\Model\Quote\Item::class,
            [
                'localeFormat' => $this->localeFormat,
                'context' => $this->modelContext,
                'statusListFactory' => $statusListFactory,
                'itemOptionFactory' => $this->itemOptionFactory,
                'quoteItemCompare' => $this->compareHelper,
                'serializer' => $this->serializer
            ]
        );
    }

    public function testGetAddress()
    {
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->setMethods(['getShippingAddress', 'getBillingAddress', 'getStoreId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $quote->expects($this->once())
            ->method('getShippingAddress')
            ->will($this->returnValue('shipping'));
        $quote->expects($this->once())
            ->method('getBillingAddress')
            ->will($this->returnValue('billing'));
        $quote->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(1));

        $this->model->setQuote($quote);

        $quote->setItemsQty(2);
        $quote->setVirtualItemsQty(1);
        $this->assertEquals('shipping', $this->model->getAddress(), 'Wrong shipping address');

        $quote->setItemsQty(2);
        $quote->setVirtualItemsQty(2);
        $this->assertEquals('billing', $this->model->getAddress(), 'Wrong billing address');
    }

    public function testSetAndQuote()
    {
        $idValue = "id_value";

        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->setMethods(['getId', 'getStoreId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $quote->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($idValue));
        $quote->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(1));

        $this->model->setQuote($quote);

        $this->assertSame($quote, $this->model->getQuote());
        $this->assertEquals($idValue, $this->model->getQuoteId());
    }

    /**
     * Tests that adding a quantity to an item without a parent item or an id will add additional quantity.
     */
    public function testAddQtyNormal()
    {
        $existingQuantity = 2;
        $quantityToAdd = 3;
        $preparedQuantityToAdd = 4;

        $this->model->setData('qty', $existingQuantity);

        $this->localeFormat->expects($this->at(0))
            ->method('getNumber')
            ->with($quantityToAdd)
            ->will($this->returnValue($preparedQuantityToAdd));

        $this->localeFormat->expects($this->at(1))
            ->method('getNumber')
            ->with($preparedQuantityToAdd + $existingQuantity)
            ->will($this->returnValue($preparedQuantityToAdd + $existingQuantity));

        $this->model->addQty($quantityToAdd);
        $this->assertEquals($preparedQuantityToAdd, $this->model->getQtyToAdd());
        $this->assertEquals($preparedQuantityToAdd + $existingQuantity, $this->model->getQty());
    }

    /**
     * Tests that adding a quantity to an item with a parent item and an id will not change the quantity.
     */
    public function testAddQtyExistingParentItemAndId()
    {
        $existingQuantity = 2;
        $quantityToAdd = 3;

        $parentItemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->setMethods(['addChild', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model->setParentItem($parentItemMock);
        $this->model->setId(1);
        $this->model->setData('qty', $existingQuantity);

        $this->model->addQty($quantityToAdd);
        $this->assertEquals($existingQuantity, $this->model->getQty());
        $this->assertNull($this->model->getQtyToAdd());
    }

    public function testSetQty()
    {
        $existingQuantity = 2;
        $quantityToAdd = 3;
        $preparedQuantityToAdd = 4;

        $this->localeFormat->expects($this->once())
            ->method('getNumber')
            ->with($quantityToAdd)
            ->will($this->returnValue($preparedQuantityToAdd));

        $this->model->setData('qty', $existingQuantity);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with('sales_quote_item_qty_set_after', ['item' => $this->model]);

        $this->model->setQty($quantityToAdd);
        $this->assertEquals($preparedQuantityToAdd, $this->model->getQty());
    }

    public function testSetQtyQuoteIgnoreOldQuantity()
    {
        $existingQuantity = 2;
        $quantityToAdd = 3;
        $preparedQuantityToAdd = 4;

        $this->localeFormat->expects($this->once())
            ->method('getNumber')
            ->with($quantityToAdd)
            ->will($this->returnValue($preparedQuantityToAdd));

        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIgnoreOldQty', 'getStoreId', '__wakeup'])
            ->getMock();
        $quoteMock->expects($this->once())
            ->method('getIgnoreOldQty')
            ->will($this->returnValue(true));
        $quoteMock->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(1));

        $this->model->setQuote($quoteMock);

        $this->model->setData('qty', $existingQuantity);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with('sales_quote_item_qty_set_after', ['item' => $this->model]);

        $this->model->setQty($quantityToAdd);
        $this->assertEquals($preparedQuantityToAdd, $this->model->getQty());
    }

    public function testSetQtyUseOldQuantity()
    {
        $existingQuantity = 2;
        $quantityToAdd = 3;
        $preparedQuantityToAdd = 4;

        $this->localeFormat->expects($this->once())
            ->method('getNumber')
            ->with($quantityToAdd)
            ->will($this->returnValue($preparedQuantityToAdd));

        $this->model->setData('qty', $existingQuantity);
        $this->model->setUseOldQty(true);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with('sales_quote_item_qty_set_after', ['item' => $this->model]);

        $this->model->setQty($quantityToAdd);
        $this->assertEquals($existingQuantity, $this->model->getQty());
    }

    public function testSetQtyOptions()
    {
        $value = ['a' => 'b'];
        $this->model->setQtyOptions($value);
        $this->assertEquals($value, $this->model->getQtyOptions());
    }

    public function testSetProduct()
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

    public function testSetProductWithQuoteAndStockItem()
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
            ->will($this->returnValue($isQtyDecimal));

        $storeId = 15;
        $customerGroupId = 11;
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId', 'getCustomerGroupId', '__wakeup'])
            ->getMock();
        $quoteMock->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue($storeId));
        $quoteMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->will($this->returnValue($customerGroupId));
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function generateProductMock(
        $productId,
        $productType,
        $productSku,
        $productName,
        $productWeight,
        $productTaxClassId,
        $productCost
    ) {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'getTypeId',
                    'getSku',
                    'getName',
                    'getWeight',
                    'getTaxClassId',
                    'getCost',
                    'setStoreId',
                    'setCustomerGroupId',
                    'getTypeInstance',
                    'getStickWithinParent',
                    'getCustomOptions',
                    'getExtensionAttributes',
                    'toArray',
                    '__wakeup',
                    'getStore',
                ]
            )
            ->getMock();

        $productMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($productId));
        $productMock->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue($productType));
        $productMock->expects($this->any())
            ->method('getSku')
            ->will($this->returnValue($productSku));
        $productMock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($productName));
        $productMock->expects($this->any())
            ->method('getWeight')
            ->will($this->returnValue($productWeight));
        $productMock->expects($this->any())
            ->method('getTaxClassId')
            ->will($this->returnValue($productTaxClassId));
        $productMock->expects($this->any())
            ->method('getCost')
            ->will($this->returnValue($productCost));
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId']);
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue(10));

        $productMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));
        $extensionAttribute = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductExtensionInterface::class)
            ->setMethods(['getStockItem'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttribute->expects($this->atLeastOnce())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));
        $productMock->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttribute);
        return $productMock;
    }

    public function testRepresentProductNoProduct()
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

    public function testRepresentProductStickWithinParentNotSameAsParentItem()
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

        $parentItemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->setMethods(['addChild', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model->setProduct($productMock);
        $this->model->setParentItem($parentItemMock);

        $productMock->expects($this->once())
            ->method('getStickWithinParent')
            ->will($this->returnValue(true));

        $this->assertFalse($this->model->representProduct($productMock));
    }

    public function testRepresentProductItemOptionsNotInProductOptions()
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

        $parentItemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->setMethods(['addChild', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model->setProduct($productMock);
        $this->model->setParentItem($parentItemMock);

        $optionCode1 = 1234;
        $optionMock1 = $this->createOptionMock($optionCode1);
        $optionMock1->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(1234));

        $optionCode2 = 7890;
        $optionMock2 = $this->createOptionMock($optionCode2);
        $optionMock2->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(7890));
        $this->model->setOptions([$optionMock1, $optionMock2]);

        $productMock->expects($this->once())
            ->method('getStickWithinParent')
            ->will($this->returnValue($parentItemMock));
        $productMock->expects($this->once())
            ->method('getCustomOptions')
            ->will($this->returnValue([$optionCode1 => $optionMock1]));

        $this->assertFalse($this->model->representProduct($productMock));
    }

    public function testRepresentProductProductOptionsNotInItemOptions()
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

        $parentItemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->setMethods(['addChild', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model->setProduct($productMock);
        $this->model->setParentItem($parentItemMock);

        $optionCode1 = 1234;
        $optionMock1 = $this->createOptionMock($optionCode1);
        $optionMock1->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(1234));

        $optionCode2 = 7890;
        $optionMock2 = $this->createOptionMock($optionCode2);
        $optionMock2->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(7890));
        $this->model->setOptions([$optionMock1]);

        $productMock->expects($this->once())
            ->method('getStickWithinParent')
            ->will($this->returnValue($parentItemMock));
        $productMock->expects($this->once())
            ->method('getCustomOptions')
            ->will($this->returnValue([$optionCode1 => $optionMock1, $optionCode2 => $optionMock2]));

        $this->assertFalse($this->model->representProduct($productMock));
    }

    public function testRepresentProductTrue()
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

        $parentItemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->setMethods(['addChild', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model->setProduct($productMock);
        $this->model->setParentItem($parentItemMock);

        $optionCode1 = 1234;
        $optionMock1 = $this->createOptionMock($optionCode1);
        $optionMock1->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(1234));

        $optionCode2 = 7890;
        $optionMock2 = $this->createOptionMock($optionCode2);
        $optionMock2->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(7890));
        $this->model->setOptions([$optionMock1, $optionMock2]);

        $productMock->expects($this->once())
            ->method('getStickWithinParent')
            ->will($this->returnValue($parentItemMock));
        $productMock->expects($this->once())
            ->method('getCustomOptions')
            ->will($this->returnValue([$optionCode1 => $optionMock1, $optionCode2 => $optionMock2]));

        $this->assertTrue($this->model->representProduct($productMock));
    }

    /**
     * test compare
     */
    public function testCompare()
    {
        $itemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $this->compareHelper->expects($this->once())
            ->method('compare')
            ->with($this->equalTo($this->model), $this->equalTo($itemMock))
            ->will($this->returnValue(true));
        $this->assertTrue($this->model->compare($itemMock));
    }

    public function testCompareOptionsEqual()
    {
        $optionCode1 = 1234;
        $optionMock1 = $this->createOptionMock($optionCode1);
        $optionMock1->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(1234));

        $this->assertTrue(
            $this->model->compareOptions([$optionCode1 => $optionMock1], [$optionCode1 => $optionMock1])
        );
    }

    public function testCompareOptionsDifferentValues()
    {
        $optionCode1 = 1234;
        $optionMock1 = $this->createOptionMock($optionCode1);
        $optionMock1->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(1234));

        $optionCode2 = 1234;
        $optionMock2 = $this->createOptionMock($optionCode1);
        $optionMock2->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(7890));

        $this->assertFalse(
            $this->model->compareOptions([$optionCode1 => $optionMock1], [$optionCode2 => $optionMock2])
        );
    }

    public function testCompareOptionsNullValues()
    {
        $optionCode1 = 1234;
        $optionMock1 = $this->createOptionMock($optionCode1);
        $optionMock1->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(1234));

        $optionCode2 = 1234;
        $optionMock2 = $this->createOptionMock($optionCode1);
        $optionMock2->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(null));

        $this->assertFalse(
            $this->model->compareOptions([$optionCode1 => $optionMock1], [$optionCode2 => $optionMock2])
        );
    }

    public function testCompareOptionsMultipleEquals()
    {
        $optionCode1 = 1234;
        $optionMock1 = $this->createOptionMock($optionCode1);
        $optionMock1->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(1234));

        $optionCode2 = 7890;
        $optionMock2 = $this->createOptionMock($optionCode1);
        $optionMock2->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(7890));

        $this->assertFalse(
            $this->model->compareOptions(
                [$optionCode1 => $optionMock1, $optionCode2 => $optionMock2],
                [$optionCode1 => $optionMock1, $optionCode2 => $optionMock2]
            )
        );
    }

    public function testGetQtyOptions()
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
            ->will($this->returnValue($productMock));

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

    public function testToArray()
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
            ->will($this->returnValue($toArrayValue));

        $data = $this->model->toArray();
        $this->assertEquals($toArrayValue, $data['product']);
    }

    public function testGetProductTypeOption()
    {
        $optionProductType = 'product_type';
        $optionProductTypeValue = 'abcd';
        $optionMock = $this->createOptionMock($optionProductType);
        $optionMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($optionProductTypeValue));
        $this->model->addOption($optionMock);

        $this->assertEquals($optionProductTypeValue, $this->model->getProductType());
    }

    public function testGetProductTypeWithProduct()
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

    public function testSetOptions()
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

    public function testSetOptionsWithNull()
    {
        $this->assertEquals($this->model, $this->model->setOptions(null));
    }

    private function createOptionMock($optionCode, $optionData = [])
    {
        $optionMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
            ->setMethods(['setData', 'setItem', 'getCode', '__wakeup', 'isDeleted', 'getValue', 'getProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock->expects($this->any())
            ->method('setData')
            ->with($optionData)
            ->will($this->returnValue($optionMock));
        $optionMock->expects($this->any())
            ->method('setItem')
            ->with($this->model)
            ->will($this->returnValue($optionMock));
        $optionMock->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($optionCode));

        return $optionMock;
    }

    public function testAddOptionArray()
    {
        $optionCode = 1234;
        $optionData = ['product' => 'test', 'code' => $optionCode];

        $optionMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
            ->setMethods(['setData', 'setItem', 'getCode', '__wakeup', 'isDeleted'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock->expects($this->once())
            ->method('setData')
            ->with($optionData)
            ->will($this->returnValue($optionMock));
        $optionMock->expects($this->once())
            ->method('setItem')
            ->with($this->model)
            ->will($this->returnValue($optionMock));
        $optionMock->expects($this->exactly(3))
            ->method('getCode')
            ->will($this->returnValue($optionCode));

        $this->itemOptionFactory->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue($optionMock));

        $this->model->addOption($optionData);
        $this->assertEquals([$optionMock], $this->model->getOptions());
        $this->assertEquals([$optionCode => $optionMock], $this->model->getOptionsByCode());
        $this->assertEquals($optionMock, $this->model->getOptionByCode($optionCode));
    }

    public function testUpdateQtyOption()
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
            \Magento\Catalog\Model\Product\Type\AbstractType::class,
            [],
            '',
            false,
            false,
            true,
            ['updateQtyOption']
        );
        $productMock->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstanceMock));

        $optionMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMock();
        $optionMock->expects($this->once())
            ->method('getProduct')
            ->will($this->returnValue($productMock));

        $quantityValue = 12;

        $this->model->setProduct($productMock);
        $typeInstanceMock->expects($this->once())
            ->method('updateQtyOption')
            ->with($this->model->getOptions(), $optionMock, $quantityValue, $productMock);
        $this->assertEquals($this->model, $this->model->updateQtyOption($optionMock, $quantityValue));
    }

    public function testRemoveOption()
    {
        $optionCode = 1234;

        $optionMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
            ->setMethods(['setItem', 'getCode', '__wakeup', 'isDeleted'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock->expects($this->once())
            ->method('setItem')
            ->with($this->model)
            ->will($this->returnValue($optionMock));
        $optionMock->expects($this->exactly(3))
            ->method('getCode')
            ->will($this->returnValue($optionCode));
        $optionMock->expects($this->at(0))
            ->method('isDeleted')
            ->will($this->returnValue(false));
        $optionMock->expects($this->at(1))
            ->method('isDeleted')
            ->will($this->returnValue(true));

        $this->model->addOption($optionMock);

        $this->assertEquals($this->model, $this->model->removeOption($optionCode));
    }

    public function testRemoveOptionNoOptionCodeExists()
    {
        $this->assertEquals($this->model, $this->model->removeOption('random'));
    }

    public function testGetOptionByCodeNonExistent()
    {
        $this->assertNull($this->model->getOptionByCode('random'));
    }

    public function testGetOptionByCodeDeletedCode()
    {
        $optionCode = 1234;

        $optionMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
            ->setMethods(['setItem', 'getCode', '__wakeup', 'isDeleted'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock->expects($this->once())
            ->method('setItem')
            ->with($this->model)
            ->will($this->returnValue($optionMock));
        $optionMock->expects($this->exactly(3))
            ->method('getCode')
            ->will($this->returnValue($optionCode));
        $optionMock->expects($this->once())
            ->method('isDeleted')
            ->will($this->returnValue(true));

        $this->model->addOption($optionMock);

        $this->assertNull($this->model->getOptionByCode($optionCode));
    }

    public function testGetOptionByCodeNotDeletedCode()
    {
        $optionCode = 1234;

        $optionMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
            ->setMethods(['setItem', 'getCode', '__wakeup', 'isDeleted'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock->expects($this->once())
            ->method('setItem')
            ->with($this->model)
            ->will($this->returnValue($optionMock));
        $optionMock->expects($this->exactly(3))
            ->method('getCode')
            ->will($this->returnValue($optionCode));
        $optionMock->expects($this->once())
            ->method('isDeleted')
            ->will($this->returnValue(false));

        $this->model->addOption($optionMock);

        $this->assertSame($optionMock, $this->model->getOptionByCode($optionCode));
    }

    public function testGetBuyRequestNoOptionByCode()
    {
        $quantity = 12;
        $this->localeFormat->expects($this->at(0))
            ->method('getNumber')
            ->with($quantity)
            ->will($this->returnValue($quantity));
        $this->model->setQty($quantity);
        $this->assertEquals($quantity, $this->model->getQty());
        $buyRequest = $this->model->getBuyRequest();
        $this->assertEquals(0, $buyRequest->getOriginalQty());
        $this->assertEquals($quantity, $buyRequest->getQty());
    }

    public function testGetBuyRequestOptionByCode()
    {
        $optionCode = "info_buyRequest";
        $buyRequestQuantity = 23;
        $optionMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
            ->setMethods(['setItem', 'getCode', '__wakeup', 'getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock->expects($this->once())
            ->method('setItem')
            ->with($this->model)
            ->will($this->returnValue($optionMock));
        $optionMock->expects($this->exactly(3))
            ->method('getCode')
            ->will($this->returnValue($optionCode));
        $optionMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue('{"qty":23}'));

        $this->model->addOption($optionMock);

        $quantity = 12;
        $this->localeFormat->expects($this->at(0))
            ->method('getNumber')
            ->with($quantity)
            ->will($this->returnValue($quantity));
        $this->model->setQty($quantity);
        $this->assertEquals($quantity, $this->model->getQty());
        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturn(json_decode($optionMock->getValue(), true));
        $buyRequest = $this->model->getBuyRequest();
        $this->assertEquals($buyRequestQuantity, $buyRequest->getOriginalQty());
        $this->assertEquals($quantity, $buyRequest->getQty());
    }

    public function testSetHasErrorFalse()
    {
        $this->errorInfos->expects($this->once())
            ->method('clear');

        $this->assertEquals($this->model, $this->model->setHasError(false));

        $this->assertFalse($this->model->getHasError());
    }

    public function testSetHasErrorTrue()
    {
        $this->errorInfos->expects($this->once())
            ->method('addItem')
            ->with(null, null, null, null);

        $this->assertEquals($this->model, $this->model->setHasError(true));

        $this->assertTrue($this->model->getHasError());
        $this->assertEquals('', $this->model->getMessage());
    }

    public function testAddErrorInfo()
    {
        $origin = 'origin';
        $code = 1;
        $message = "message";
        $additionalData = new \Magento\Framework\DataObject();
        $additionalData->setTemp(true);

        $this->errorInfos->expects($this->once())
            ->method('addItem')
            ->with($origin, $code, $message, $additionalData);

        $this->assertEquals($this->model, $this->model->addErrorInfo($origin, $code, $message, $additionalData));

        $this->assertTrue($this->model->getHasError());
        $this->assertEquals($message, $this->model->getMessage());
    }

    public function testGetErrorInfos()
    {
        $retValue = 'return value';

        $this->errorInfos->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue($retValue));

        $this->assertEquals($retValue, $this->model->getErrorInfos());
    }

    public function testRemoveErrorInfosByParams()
    {
        $message = "message";
        $message2 = "message2";

        $this->errorInfos->expects($this->at(0))
            ->method('addItem')
            ->with(null, null, $message);
        $this->errorInfos->expects($this->at(1))
            ->method('addItem')
            ->with(null, null, $message2);
        $this->assertEquals($this->model, $this->model->addErrorInfo(null, null, $message));
        $this->assertEquals($this->model, $this->model->addErrorInfo(null, null, $message2));
        $this->assertEquals($message . "\n" . $message2, $this->model->getMessage());

        $params = [];
        $removedItems = [['message' => $message]];

        $this->errorInfos->expects($this->once())
            ->method('removeItemsByParams')
            ->with($params)
            ->will($this->returnValue($removedItems));

        $this->errorInfos->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue(true));

        $this->assertEquals($this->model, $this->model->removeErrorInfosByParams($params));
        $this->assertEquals($message2, $this->model->getMessage());
    }

    public function testRemoveErrorInfosByParamsAllErrorsRemoved()
    {
        $message = "message";
        $message2 = "message2";

        $this->errorInfos->expects($this->at(0))
            ->method('addItem')
            ->with(null, null, $message);
        $this->errorInfos->expects($this->at(1))
            ->method('addItem')
            ->with(null, null, $message2);
        $this->assertEquals($this->model, $this->model->addErrorInfo(null, null, $message));
        $this->assertEquals($this->model, $this->model->addErrorInfo(null, null, $message2));
        $this->assertEquals($message . "\n" . $message2, $this->model->getMessage());

        $params = [];
        $removedItems = [['message' => $message], ['message' => $message2]];

        $this->errorInfos->expects($this->once())
            ->method('removeItemsByParams')
            ->with($params)
            ->will($this->returnValue($removedItems));

        $this->errorInfos->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue([]));

        $this->assertEquals($this->model, $this->model->removeErrorInfosByParams($params));
        $this->assertFalse($this->model->getHasError());
        $this->assertEquals('', $this->model->getMessage());
    }
}
