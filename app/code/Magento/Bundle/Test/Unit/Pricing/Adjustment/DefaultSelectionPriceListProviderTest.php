<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Pricing\Adjustment;

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
        $this->selectionFactory = $this->getMockBuilder(BundleSelectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogData = $this->getMockBuilder(CatalogData::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->websiteRepository = $this->getMockBuilder(WebsiteRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPriceType'])
            ->onlyMethods(['getTypeInstance', 'isSalable'])
            ->getMock();
        $this->optionsCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeInstance = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->option = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectionCollection = $this->getMockBuilder(SelectionCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->selection = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->store = $this->getMockBuilder(StoreInterface::class)
            ->getMockForAbstractClass();
        $this->website = $this->getMockBuilder(WebsiteInterface::class)
            ->getMockForAbstractClass();

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
        $this->product->expects($this->any())
            ->method('getTypeInstance')
            ->willReturn($this->typeInstance);
        $this->product->expects($this->once())
            ->method('getPriceType')->willReturn(Price::PRICE_TYPE_FIXED);
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

    public function testGetPriceListForFixedPriceType(): void
    {
        $optionId = 1;

        $this->typeInstance->expects($this->any())
            ->method('getOptionsCollection')
            ->with($this->product)
            ->willReturn($this->optionsCollection);
        $this->product->expects($this->any())
            ->method('getTypeInstance')
            ->willReturn($this->typeInstance);
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
        $this->optionsCollection->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$option]));
        $this->typeInstance->expects($this->any())
            ->method('getOptionsCollection')
            ->with($this->product)
            ->willReturn($this->optionsCollection);
        $this->product->expects($this->any())
            ->method('getTypeInstance')
            ->willReturn($this->typeInstance);
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
        $this->product->expects($this->once())->method('isSalable')->willReturn(true);
        $this->optionsCollection->expects($this->once())
            ->method('getSize')
            ->willReturn(1);
        $this->optionsCollection->expects($this->once())
            ->method('addFilter')
            ->willReturn($this->optionsCollection);

        $this->model->getPriceList($this->product, true, false);
    }
}
