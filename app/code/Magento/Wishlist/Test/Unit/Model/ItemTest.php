<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);


namespace Magento\Wishlist\Test\Unit\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Item\Option;
use Magento\Wishlist\Model\Item\OptionFactory;
use Magento\Wishlist\Model\ResourceModel\Item\Collection;
use Magento\Wishlist\Model\ResourceModel\Item\Option\CollectionFactory;
use Magento\Wishlist\Model\ResourceModel\Item as ItemResource;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Registry|MockObject
     */
    protected $registry;

    /**
     * @var Url|MockObject
     */
    protected $catalogUrl;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $productTypeConfig;

    /**
     * @var ItemResource|MockObject
     */
    protected $resource;

    /**
     * @var Collection|MockObject
     */
    protected $collection;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var DateTime|MockObject
     */
    protected $date;

    /**
     * @var OptionFactory|MockObject
     */
    protected $optionFactory;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $itemOptFactory;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    protected $productRepository;

    /**
     * @var Item
     */
    protected $model;

    /**
     * @var Json
     */
    protected $serializer;

    protected function setUp(): void
    {
        $context = $this->createMock(Context::class);
        $this->registry = $this->createMock(Registry::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->date = $this->createMock(DateTime::class);
        $this->catalogUrl = $this->createMock(Url::class);
        $this->optionFactory = $this->createPartialMock(OptionFactory::class, ['create']);
        $this->itemOptFactory = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->productTypeConfig = $this->createMock(ConfigInterface::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->resource = $this->createMock(ItemResource::class);
        $this->collection = $this->createMock(Collection::class);

        $this->serializer = $this->createMock(Json::class);

        $this->model = new Item(
            $context,
            $this->registry,
            $this->storeManager,
            $this->date,
            $this->catalogUrl,
            $this->optionFactory,
            $this->itemOptFactory,
            $this->productTypeConfig,
            $this->productRepository,
            $this->resource,
            $this->collection,
            [],
            $this->serializer
        );
    }

    /**
     */
    #[DataProvider('getOptionsDataProvider')]
    public function testAddGetOptions($code, $option)
    {
        if (is_callable($option)) {
            $option = $option($this);
        }
        $this->assertEmpty($this->model->getOptions());
        $optionMock = $this->createPartialMockWithReflection(
            Option::class,
            ['getCode', 'setData', '__wakeup']
        );
        $optionMock->expects($this->any())
            ->method('setData')
            ->willReturnSelf();
        $optionMock->expects($this->any())
            ->method('getCode')
            ->willReturn($code);

        $this->optionFactory->expects($this->any())
            ->method('create')
            ->willReturn($optionMock);
        $this->model->addOption($option);
        $this->assertCount(1, $this->model->getOptions());
    }

    /**
     */
    #[DataProvider('getOptionsDataProvider')]
    public function testRemoveOptionByCode($code, $option)
    {
        if (is_callable($option)) {
            $option = $option($this);
        }
        $this->assertEmpty($this->model->getOptions());
        $optionMock = $this->createPartialMockWithReflection(
            Option::class,
            ['getCode', 'setData', '__wakeup']
        );
        $optionMock->expects($this->any())
            ->method('setData')
            ->willReturnSelf();
        $optionMock->expects($this->any())
            ->method('getCode')
            ->willReturn($code);

        $this->optionFactory->expects($this->any())
            ->method('create')
            ->willReturn($optionMock);
        $this->model->addOption($option);
        $this->assertCount(1, $this->model->getOptions());
        $this->model->removeOption($code);
        $actualOptions = $this->model->getOptions();
        $actualOption = array_pop($actualOptions);
        $this->assertTrue($actualOption->isDeleted());
    }

    protected function getMockForOptionClass()
    {
        $optionMock = $this->createPartialMockWithReflection(
            Option::class,
            ['getCode', '__wakeup']
        );
        $optionMock->expects($this->any())
            ->method('getCode')
            ->willReturn('second_key');
        return $optionMock;
    }

    protected function getMockForProductClass()
    {
        $productMock = $this->createMock(Product::class);
        return new DataObject(['code' => 'third_key', 'product' => $productMock]);
    }

    /**
     * @return array
     */
    public static function getOptionsDataProvider()
    {
        $optionMock = static fn (self $testCase) => $testCase->getMockForOptionClass();

        $productMock = static fn (self $testCase) => $testCase->getMockForProductClass();
        return [
            ['first_key', ['code' => 'first_key', 'value' => 'first_data']],
            ['second_key', $optionMock],
            ['third_key', $productMock],
        ];
    }

    public function testCompareOptionsPositive()
    {
        $code = 'someOption';
        $optionValue = 100;
        $optionsOneMock = $this->createPartialMockWithReflection(
            QuoteItem::class,
            ['getCode', 'getValue', '__wakeup']
        );
        $optionsTwoMock = $this->createPartialMockWithReflection(
            QuoteItem::class,
            ['getValue', '__wakeup']
        );

        $optionsOneMock->expects($this->once())->method('getCode')->willReturn($code);
        $optionsOneMock->expects($this->once())->method('getValue')->willReturn($optionValue);
        $optionsTwoMock->expects($this->once())->method('getValue')->willReturn($optionValue);

        $result = $this->model->compareOptions(
            [$code => $optionsOneMock],
            [$code => $optionsTwoMock]
        );

        $this->assertTrue($result);
    }

    public function testCompareOptionsNegative()
    {
        $code = 'someOption';
        $optionOneValue = 100;
        $optionTwoValue = 200;
        $optionsOneMock = $this->createPartialMockWithReflection(
            QuoteItem::class,
            ['getCode', 'getValue', '__wakeup']
        );
        $optionsTwoMock = $this->createPartialMockWithReflection(
            QuoteItem::class,
            ['getValue', '__wakeup']
        );

        $optionsOneMock->expects($this->once())->method('getCode')->willReturn($code);
        $optionsOneMock->expects($this->once())->method('getValue')->willReturn($optionOneValue);
        $optionsTwoMock->expects($this->once())->method('getValue')->willReturn($optionTwoValue);

        $result = $this->model->compareOptions(
            [$code => $optionsOneMock],
            [$code => $optionsTwoMock]
        );

        $this->assertFalse($result);
    }

    public function testCompareOptionsNegativeOptionsTwoHaveNotOption()
    {
        $code = 'someOption';
        $optionsOneMock = $this->createPartialMockWithReflection(
            QuoteItem::class,
            ['getCode', '__wakeup']
        );
        $optionsTwoMock = $this->createPartialMockWithReflection(
            QuoteItem::class,
            ['__wakeup']
        );

        $optionsOneMock->expects($this->once())->method('getCode')->willReturn($code);

        $result = $this->model->compareOptions(
            [$code => $optionsOneMock],
            ['someOneElse' => $optionsTwoMock]
        );

        $this->assertFalse($result);
    }

    public function testSetAndSaveItemOptions()
    {
        $this->assertEmpty($this->model->getOptions());
        $firstOptionMock = $this->createPartialMockWithReflection(
            Option::class,
            ['getCode', 'isDeleted', 'delete', '__wakeup']
        );
        $firstOptionMock->expects($this->any())
            ->method('getCode')
            ->willReturn('first_code');
        $firstOptionMock->expects($this->any())
            ->method('isDeleted')
            ->willReturn(true);
        $firstOptionMock->expects($this->once())
            ->method('delete');

        $secondOptionMock = $this->createPartialMockWithReflection(
            Option::class,
            ['getCode', 'save', '__wakeup']
        );
        $secondOptionMock->expects($this->any())
            ->method('getCode')
            ->willReturn('second_code');
        $secondOptionMock->expects($this->once())
            ->method('save');

        $this->model->setOptions([$firstOptionMock, $secondOptionMock]);
        $this->assertNull($this->model->isOptionsSaved());
        $this->model->saveItemOptions();
        $this->assertTrue($this->model->isOptionsSaved());
    }

    public function testGetProductWithException()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Cannot specify product.');
        $this->model->getProduct();
    }

    public function testGetProduct()
    {
        $productId = 1;
        $storeId = 0;
        $this->model->setData('product_id', $productId);
        $this->model->setData('store_id', $storeId);
        $productMock = $this->createPartialMock(
            Product::class,
            ['setCustomOptions', 'setFinalPrice']
        );
        $productMock->expects($this->any())
            ->method('setFinalPrice')
            ->with(null);
        $productMock->expects($this->any())
            ->method('setCustomOptions')
            ->with([]);
        $this->productRepository->expects($this->once())
            ->method('getById')
            ->with($productId, false, $storeId, true)
            ->willReturn($productMock);
        $this->assertEquals($productMock, $this->model->getProduct());
    }
}
