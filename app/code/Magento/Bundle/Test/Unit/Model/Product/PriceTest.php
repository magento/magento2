<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Product;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\ResourceModel\Selection\Collection;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Pricing\SpecialPriceService;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[CoversClass(Price::class)]
class PriceTest extends TestCase
{
    use MockCreationTrait;

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
     * @var Data|MockObject
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
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->ruleFactoryMock = $this->createPartialMock(
            RuleFactory::class,
            ['create']
        );
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->localeDateMock = $this->createMock(TimezoneInterface::class);
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->catalogHelperMock = $this->createMock(Data::class);
        $this->storeMock = $this->createPartialMockWithReflection(
            Store::class,
            ['setBaseCurrencyCode', 'getBaseCurrencyCode']
        );
        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);
        $this->groupManagement = $this->createMock(GroupManagementInterface::class);
        $tpFactory = $this->createPartialMock(
            ProductTierPriceInterfaceFactory::class,
            ['create']
        );
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->serializer = $this->createMock(Json::class);
        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode((string)$value, true);
                }
            );
        $tierPriceExtensionFactoryMock = $this->createPartialMock(ProductTierPriceExtensionFactory::class, ['create']);

        $specialPriceService = $this->createMock(SpecialPriceService::class);

        $specialPriceService->expects($this->any())
            ->method('execute')
            ->willReturnCallback(function ($value) {
                return $value;
            });

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
                'tierPriceExtensionFactory' => $tierPriceExtensionFactoryMock,
                'specialPriceService' => $specialPriceService
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
     * @return void
     */
    #[DataProvider('calculateSpecialPrice')]
    public function testCalculateSpecialPrice($finalPrice, $specialPrice, $callsNumber, $dateInInterval, $expected)
    {
        $this->localeDateMock->expects($this->exactly($callsNumber))
            ->method('isScopeDateInInterval')->willReturn($dateInInterval);

        $this->storeManagerMock->method('getStore')->willReturn($this->storeMock);

        $this->storeMock->setRoundPriceCallback(function ($price) {
            return $price;
        });

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
    public static function calculateSpecialPrice()
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
        $productMock = $this->createMock(Product::class);

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->willReturn(false);

        $this->assertEquals(0, $this->model->getTotalBundleItemsPrice($productMock));
    }

    /**
     * Test for getTotalBundleItemsPrice() with empty options.
     *
     * @param string|null $value
     * @return void
     */
    #[DataProvider('dataProviderWithEmptyOptions')]
    public function testGetTotalBundleItemsPriceWithEmptyOptions($value)
    {
        $dataObjectMock = new DataObject();

        $productMock = $this->createMock(Product::class);

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->willReturn(true);
        $productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('bundle_selection_ids')
            ->willReturn($dataObjectMock);

        $dataObjectMock->setValue($value);
        $this->assertEquals(0, $this->model->getTotalBundleItemsPrice($productMock));
    }

    /**
     * Data provider for getTotalBundleItemsPrice() with empty options.
     *
     * @return array
     */
    public static function dataProviderWithEmptyOptions()
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

        $dataObjectMock = new DataObject();

        $productMock = $this->createMock(Product::class);

        $productTypeMock = $this->createMock(Type::class);

        $selectionsMock = $this->createMock(Collection::class);

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

        $dataObjectMock->setValue('{"0":1}');

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
