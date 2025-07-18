<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemTest extends TestCase
{
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
     * @var \Magento\Wishlist\Model\ResourceModel\Item|MockObject
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
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        $this->date = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogUrl = $this->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionFactory = $this->getMockBuilder(OptionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->itemOptFactory =
            $this->getMockBuilder(CollectionFactory::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['create'])
                ->getMock();
        $this->productTypeConfig = $this->getMockBuilder(ConfigInterface::class)
            ->getMock();
        $this->productRepository = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->resource = $this->getMockBuilder(\Magento\Wishlist\Model\ResourceModel\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializer = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

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
     * @dataProvider getOptionsDataProvider
     */
    public function testAddGetOptions($code, $option)
    {
        if (is_callable($option)) {
            $option = $option($this);
        }
        $this->assertEmpty($this->model->getOptions());
        $optionMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCode'])
            ->onlyMethods(['setData', '__wakeup'])
            ->getMock();
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
     * @dataProvider getOptionsDataProvider
     */
    public function testRemoveOptionByCode($code, $option)
    {
        if (is_callable($option)) {
            $option = $option($this);
        }
        $this->assertEmpty($this->model->getOptions());
        $optionMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCode'])
            ->onlyMethods(['setData', '__wakeup'])
            ->getMock();
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

    protected function getMockForOptionClass() {
        $optionMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCode'])
            ->onlyMethods(['__wakeup'])
            ->getMock();
        $optionMock->expects($this->any())
            ->method('getCode')
            ->willReturn('second_key');
        return $optionMock;
    }

    protected function getMockForProductClass() {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $optionsOneMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCode', 'getValue'])
            ->onlyMethods(['__wakeup'])
            ->getMock();
        $optionsTwoMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['getValue'])
            ->onlyMethods(['__wakeup'])
            ->getMock();

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
        $optionsOneMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCode', 'getValue'])
            ->onlyMethods(['__wakeup'])
            ->getMock();
        $optionsTwoMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['getValue'])
            ->onlyMethods(['__wakeup'])
            ->getMock();

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
        $optionsOneMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCode'])
            ->onlyMethods(['__wakeup'])
            ->getMock();
        $optionsTwoMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__wakeup'])
            ->getMock();

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
        $firstOptionMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCode'])
            ->onlyMethods(['isDeleted', 'delete', '__wakeup'])
            ->getMock();
        $firstOptionMock->expects($this->any())
            ->method('getCode')
            ->willReturn('first_code');
        $firstOptionMock->expects($this->any())
            ->method('isDeleted')
            ->willReturn(true);
        $firstOptionMock->expects($this->once())
            ->method('delete');

        $secondOptionMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCode'])
            ->onlyMethods(['save', '__wakeup'])
            ->getMock();
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
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setCustomOptions', 'setFinalPrice'])
            ->getMock();
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
