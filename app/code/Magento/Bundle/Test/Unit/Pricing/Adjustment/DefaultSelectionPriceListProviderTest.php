<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Pricing\Adjustment;

use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Bundle\Model\ResourceModel\Selection\Collection as SelectionCollection;
use Magento\Bundle\Pricing\Adjustment\DefaultSelectionPriceListProvider;
use Magento\Bundle\Pricing\Price\BundleSelectionFactory;
use Magento\Catalog\Helper\Data as CatalogData;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Bundle\Pricing\DefaultSelectionPriceListProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DefaultSelectionPriceListProviderTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var BundleSelectionFactory|MockObject
     */
    private $selectionFactory;

    /**
     * @var CatalogData|MockObject
     */
    private $catalogData;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var WebsiteRepositoryInterface|MockObject
     */
    private $websiteRepository;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var Collection|MockObject
     */
    private $optionsCollection;

    /**
     * @var Type|MockObject
     */
    private $typeInstance;

    /**
     * @var Option|MockObject
     */
    private $option;

    /**
     * @var SelectionCollection|MockObject
     */
    private $selectionCollection;

    /**
     * @var DataObject|MockObject
     */
    private $selection;

    /**
     * @var StoreInterface|MockObject
     */
    private $store;

    /**
     * @var WebsiteInterface|MockObject
     */
    private $website;

    /**
     * @var DefaultSelectionPriceListProvider
     */
    private $model;

    protected function setUp(): void
    {
        $this->selectionFactory = $this->createMock(BundleSelectionFactory::class);
        $this->catalogData = $this->createMock(CatalogData::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->websiteRepository = $this->createMock(WebsiteRepositoryInterface::class);

        /** @var Product */
        $this->product = $this->createPartialMockWithReflection(
            Product::class,
            [
                'setTypeInstance', 'getTypeInstance', 'setPriceType', 'getPriceType',
                'setIsSalable', 'getIsSalable', 'isSalable'
            ]
        );
        $this->optionsCollection = $this->createMock(Collection::class);
        $this->typeInstance = $this->createMock(Type::class);
        $this->option = $this->createMock(Option::class);
        $this->selectionCollection = $this->createMock(SelectionCollection::class);
        $this->selection = $this->createMock(DataObject::class);
        $this->store = $this->createMock(StoreInterface::class);
        $this->website = $this->createMock(WebsiteInterface::class);

        $this->model = new DefaultSelectionPriceListProvider(
            $this->selectionFactory,
            $this->catalogData,
            $this->storeManager,
            $this->websiteRepository
        );
    }

    public function testGetPriceList(): void
    {
        $optionId = 1;

        $this->typeInstance->expects($this->any())
            ->method('getOptionsCollection')
            ->with($this->product)
            ->willReturn($this->optionsCollection);
        $this->product->method('getTypeInstance')->willReturn($this->typeInstance);
        $this->product->method('getPriceType')->willReturn(Price::PRICE_TYPE_FIXED);
        $this->optionsCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->option]));
        $this->option->expects($this->once())
            ->method('getOptionId')
            ->willReturn($optionId);
        $this->typeInstance->expects($this->once())
            ->method('getSelectionsCollection')
            ->with([$optionId], $this->product)
            ->willReturn($this->selectionCollection);
        $this->option->expects($this->once())
            ->method('isMultiSelection')
            ->willReturn(true);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(0);
        $this->websiteRepository->expects($this->once())
            ->method('getDefault')
            ->willReturn($this->website);
        $this->website->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->selectionCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));
        $this->selectionCollection->expects($this->never())
            ->method('setFlag')
            ->with('has_stock_status_filter', true);

        $this->model->getPriceList($this->product, false, false);
    }

    #[DataProvider('dataProvider')]
    public function testGetPriceListForFixedPriceType($websiteId): void
    {
        $optionId = 1;

        $this->typeInstance->expects($this->any())
            ->method('getOptionsCollection')
            ->with($this->product)
            ->willReturn($this->optionsCollection);
        $this->product->method('getTypeInstance')->willReturn($this->typeInstance);
        $this->optionsCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->option]));
        $this->option->expects($this->once())
            ->method('getOptionId')
            ->willReturn($optionId);
        $this->typeInstance->expects($this->once())
            ->method('getSelectionsCollection')
            ->with([$optionId], $this->product)
            ->willReturn($this->selectionCollection);
        $this->option->expects($this->once())
            ->method('isMultiSelection')
            ->willReturn(true);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);

        if ($websiteId) {
            $this->websiteRepository->expects($this->never())
                ->method('getDefault');
        } else {
            $this->websiteRepository->expects($this->once())
                ->method('getDefault')
                ->willReturn($this->website);
            $this->website->expects($this->once())
                ->method('getId')
                ->willReturn(1);
        }
        $this->selectionCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));
        $this->selectionCollection->expects($this->once())
            ->method('setFlag')
            ->with('has_stock_status_filter', true);

        $this->model->getPriceList($this->product, false, false);
    }

    public function testGetPriceListWithSearchMin(): void
    {
        $option = $this->createMock(Option::class);
        $option->expects($this->once())->method('getRequired')
            ->willReturn(true);
        $this->optionsCollection->method('getIterator')->willReturn(new \ArrayIterator([$option]));
        $this->typeInstance->expects($this->any())
            ->method('getOptionsCollection')
            ->with($this->product)
            ->willReturn($this->optionsCollection);
        $this->product->method('getTypeInstance')->willReturn($this->typeInstance);
        $this->selectionCollection->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($this->createMock(Product::class));
        $this->typeInstance->expects($this->once())
            ->method('getSelectionsCollection')
            ->willReturn($this->selectionCollection);
        $this->selectionCollection->expects($this->once())
            ->method('setFlag')
            ->with('has_stock_status_filter', true);
        $this->selectionCollection->expects($this->once())
            ->method('addQuantityFilter');
        $this->product->method('isSalable')->willReturn(true);
        $this->optionsCollection->expects($this->once())
            ->method('getSize')
            ->willReturn(1);
        $this->optionsCollection->expects($this->once())
            ->method('addFilter')
            ->willReturn($this->optionsCollection);

        $this->model->getPriceList($this->product, true, false);
    }

    public static function dataProvider()
    {
        return [
            'website provided' => [1],
            'website not provided' => [0]
        ];
    }
}
