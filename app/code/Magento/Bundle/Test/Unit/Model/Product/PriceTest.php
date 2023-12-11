<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Product;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\ResourceModel\Selection\Collection;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceTest extends TestCase
{
    /**
     * @var RuleFactory|MockObject
     */
    private $ruleFactoryMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $localeDateMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Session|MockObject
     */
    private $customerSessionMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Catalog\Helper\Data|MockObject
     */
    private $catalogHelperMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @var Price
     */
    private $model;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrency;

    /**
     * @var GroupManagementInterface|MockObject
     */
    private $groupManagement;

    /**
     * Serializer interface instance.
     *
     * @var Json
     */
    private $serializer;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->ruleFactoryMock = $this->createPartialMock(
            RuleFactory::class,
            ['create']
        );
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->localeDateMock = $this->getMockForAbstractClass(TimezoneInterface::class);
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->catalogHelperMock = $this->createMock(Data::class);
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->addMethods(['roundPrice'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceCurrency = $this->getMockBuilder(
            PriceCurrencyInterface::class
        )->getMock();
        $this->groupManagement = $this->getMockBuilder(GroupManagementInterface::class)
            ->getMockForAbstractClass();
        $tpFactory = $this->createPartialMock(
            ProductTierPriceInterfaceFactory::class,
            ['create']
        );
        $scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->serializer = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode((string)$value, true);
                }
            );
        $tierPriceExtensionFactoryMock = $this->getMockBuilder(ProductTierPriceExtensionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            Price::class,
            [
                'ruleFactory' => $this->ruleFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'localeDate' => $this->localeDateMock,
                'customerSession' => $this->customerSessionMock,
                'eventManager' => $this->eventManagerMock,
                'priceCurrency' => $this->priceCurrency,
                'groupManagement' => $this->groupManagement,
                'tierPriceFactory' => $tpFactory,
                'config' => $scopeConfig,
                'catalogData' => $this->catalogHelperMock,
                'serializer' => $this->serializer,
                'tierPriceExtensionFactory' => $tierPriceExtensionFactoryMock
            ]
        );
    }

    /**
     * Test for calculateSpecialPrice().
     *
     * @param float $finalPrice
     * @param float $specialPrice
     * @param int $callsNumber
     * @param bool $dateInInterval
     * @param float $expected
     *
     * @covers \Magento\Bundle\Model\Product\Price::calculateSpecialPrice
     * @covers \Magento\Bundle\Model\Product\Price::__construct
     * @dataProvider calculateSpecialPrice
     * @return void
     */
    public function testCalculateSpecialPrice($finalPrice, $specialPrice, $callsNumber, $dateInInterval, $expected)
    {
        $this->localeDateMock->expects($this->exactly($callsNumber))
            ->method('isScopeDateInInterval')->willReturn($dateInInterval);

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')->willReturn($this->storeMock);

        $this->storeMock->expects($this->any())
            ->method('roundPrice')->willReturnArgument(0);

        $this->assertEquals(
            $expected,
            $this->model->calculateSpecialPrice($finalPrice, $specialPrice, date('Y-m-d'), date('Y-m-d'))
        );
    }

    /**
     * Data provider for calculateSpecialPrice() test.
     *
     * @return array
     */
    public function calculateSpecialPrice()
    {
        return [
            [10, null, 0, true, 10],
            [10, false, 0, true, 10],
            [10, 50, 1, false, 10],
            [10, 50, 1, true, 5],
            [0, 50, 1, true, 0],
            [10, 100, 1, true, 10],
        ];
    }

    /**
     * Test for getTotalBundleItemsPrice() with noCustom options.
     *
     * @return void
     */
    public function testGetTotalBundleItemsPriceWithNoCustomOptions()
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->willReturn(false);

        $this->assertEquals(0, $this->model->getTotalBundleItemsPrice($productMock));
    }

    /**
     * Test for getTotalBundleItemsPrice() with empty options.
     *
     * @param string|null $value
     * @dataProvider dataProviderWithEmptyOptions
     * @return void
     */
    public function testGetTotalBundleItemsPriceWithEmptyOptions($value)
    {
        $dataObjectMock = $this->getMockBuilder(DataObject::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->willReturn(true);
        $productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('bundle_selection_ids')
            ->willReturn($dataObjectMock);

        $dataObjectMock->expects($this->once())
            ->method('getValue')
            ->willReturn($value);
        $this->assertEquals(0, $this->model->getTotalBundleItemsPrice($productMock));
    }

    /**
     * Data provider for getTotalBundleItemsPrice() with empty options.
     *
     * @return array
     */
    public function dataProviderWithEmptyOptions()
    {
        return [
            ['{}'],
            [''],
            [null],
        ];
    }

    /**
     * Test for getTotalBundleItemsPrice() with empty options.
     *
     * @return void
     */
    public function testGetTotalBundleItemsPriceWithNoItems()
    {
        $storeId = 1;

        $dataObjectMock = $this->getMockBuilder(DataObject::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeMock = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();

        $selectionsMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->willReturn(true);
        $productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('bundle_selection_ids')
            ->willReturn($dataObjectMock);
        $productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productTypeMock);
        $productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $dataObjectMock->expects($this->once())
            ->method('getValue')
            ->willReturn('{"0":1}');

        $productTypeMock->expects($this->once())
            ->method('getSelectionsByIds')
            ->with([1], $productMock)
            ->willReturn($selectionsMock);

        $selectionsMock->expects($this->once())
            ->method('addTierPriceData')
            ->willReturnSelf();
        $selectionsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'prepare_catalog_product_collection_prices',
                ['collection' => $selectionsMock, 'store_id' => $storeId]
            )
            ->willReturnSelf();

        $this->assertEquals(0, $this->model->getTotalBundleItemsPrice($productMock));
    }
}
