<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model\Product;

use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\RuleFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $ruleFactoryMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $localeDateMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerSessionMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Catalog\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    private $catalogHelperMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeMock;

    /**
     * @var \Magento\Bundle\Model\Product\Price
     */
    private $model;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $priceCurrency;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $groupManagement;

    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
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
            \Magento\CatalogRule\Model\ResourceModel\RuleFactory::class,
            ['create']
        );
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->localeDateMock = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->catalogHelperMock = $this->createMock(\Magento\Catalog\Helper\Data::class);
        $this->storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['roundPrice']);
        $this->priceCurrency = $this->getMockBuilder(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        )->getMock();
        $this->groupManagement = $this->getMockBuilder(\Magento\Customer\Api\GroupManagementInterface::class)
            ->getMockForAbstractClass();
        $tpFactory = $this->createPartialMock(
            \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory::class,
            ['create']
        );
        $scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );
        $tierPriceExtensionFactoryMock = $this->getMockBuilder(ProductTierPriceExtensionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Bundle\Model\Product\Price::class,
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
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
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
        $dataObjectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
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

        $dataObjectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeMock = $this->getMockBuilder(\Magento\Bundle\Model\Product\Type::class)
            ->disableOriginalConstructor()
            ->getMock();

        $selectionsMock = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
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
