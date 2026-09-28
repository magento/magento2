<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Category\CollectionFilter;
use Magento\Catalog\Model\Layer\Category\StateKey;
use Magento\Catalog\Model\Layer\ContextInterface;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Magento\Catalog\Model\Layer\State;
use Magento\Catalog\Model\Layer\StateFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class LayerTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Layer
     */
    private $model;

    /**
     * @var Category|MockObject
     */
    private $category;

    /**
     * @var Registry|MockObject
     */
    private $registry;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Store|MockObject
     */
    private $store;

    /**
     * @var ContextInterface|MockObject
     */
    private $context;

    /**
     * @var StateKey|MockObject
     */
    private $stateKeyGenerator;

    /**
     * @var StateFactory|MockObject
     */
    private $stateFactory;

    /**
     * @var State|MockObject
     */
    private $state;

    /**
     * @var CollectionFilter|MockObject
     */
    private $collectionFilter;

    /**
     * @var Collection|MockObject
     */
    private $collection;

    /**
     * @var ItemCollectionProviderInterface|MockObject
     */
    private $collectionProvider;

    /**
     * @var Item|MockObject
     */
    private $filter;

    /**
     * @var AbstractFilter|MockObject
     */
    private $abstractFilter;

    /**
     * @var CategoryRepositoryInterface|MockObject
     */
    private $categoryRepository;

    /**
     * @var Category|MockObject
     */
    private $currentCategory;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->category = $this->createPartialMock(Category::class, ['getId']);

        $this->registry = $this->createPartialMock(Registry::class, ['registry']);

        $this->store = $this->createPartialMockWithReflection(Store::class, ['setRootCategoryId', 'getRootCategoryId']);
        $rootCategoryId = null;
        $this->store->method('setRootCategoryId')->willReturnCallback(function ($id) use (&$rootCategoryId) {
            $rootCategoryId = $id;
        });
        $this->store->method('getRootCategoryId')->willReturnCallback(function () use (&$rootCategoryId) {
            return $rootCategoryId;
        });

        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->storeManager->method('getStore')->willReturn($this->store);

        $this->stateKeyGenerator = $this->createPartialMock(StateKey::class, ['toString']);

        $this->collectionFilter = $this->createPartialMock(CollectionFilter::class, ['filter']);

        $this->collectionProvider = $this->createMock(ItemCollectionProviderInterface::class);

        $this->filter = $this->createPartialMock(Item::class, ['getFilter', 'getValueString']);

        $this->abstractFilter = $this->createPartialMock(AbstractFilter::class, ['getRequestVar']);

        $this->context = $this->createMock(ContextInterface::class);
        $this->context->method('getStateKey')->willReturn($this->stateKeyGenerator);
        $this->context->method('getCollectionFilter')->willReturn($this->collectionFilter);
        $this->context->method('getCollectionProvider')->willReturn($this->collectionProvider);

        $this->state = $this->createMock(State::class);

        $this->stateFactory = $this->createPartialMock(StateFactory::class, ['create']);
        $this->stateFactory->method('create')->willReturn($this->state);

        $this->collection = $this->createMock(Collection::class);

        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->currentCategory = $this->createPartialMock(
            Category::class,
            ['getId']
        );

        $this->model = $helper->getObject(
            Layer::class,
            [
                'registry' => $this->registry,
                'storeManager' => $this->storeManager,
                'context' => $this->context,
                'layerStateFactory' => $this->stateFactory,
                'categoryRepository' => $this->categoryRepository,
            ]
        );
    }

    public function testGetState()
    {
        $this->assertInstanceOf(State::class, $this->model->getState());
    }

    public function testGetStateKey()
    {
        $stateKey = 'sk';
        $this->registry->expects($this->once())->method('registry')->with('current_category')
            ->willReturn($this->category);

        $this->stateKeyGenerator->expects($this->once())->method('toString')
            ->with($this->category)
            ->willReturn($stateKey);

        $this->assertEquals($stateKey, $this->model->getStateKey());
    }

    public function testGetProductCollection()
    {
        $this->registry->expects($this->once())->method('registry')->with('current_category')
            ->willReturn($this->category);

        $this->category->method('getId')->willReturn(333);

        $this->collectionFilter->expects($this->once())->method('filter')
            ->with($this->collection, $this->category);

        $this->collectionProvider->expects($this->once())->method('getCollection')
            ->with($this->category)
            ->willReturn($this->collection);

        $result = $this->model->getProductCollection();
        $this->assertInstanceOf(Collection::class, $result);
        $result = $this->model->getProductCollection();
        $this->assertInstanceOf(Collection::class, $result);
    }

    public function testApply()
    {
        $stateKey = 'sk';
        $this->registry->expects($this->once())->method('registry')->with('current_category')
            ->willReturn($this->category);

        $this->stateKeyGenerator->expects($this->once())->method('toString')
            ->with($this->category)
            ->willReturn($stateKey);

        $this->state->method('getFilters')->willReturn([$this->filter]);

        $this->filter->expects($this->once())->method('getFilter')->willReturn($this->abstractFilter);
        $this->filter->expects($this->once())->method('getValueString')->willReturn('t');

        $this->abstractFilter->expects($this->once())->method('getRequestVar')->willReturn('t');

        $result = $this->model->apply();
        $this->assertInstanceOf(Layer::class, $result);
    }

    public function testPrepareProductCollection()
    {
        $this->registry->expects($this->once())->method('registry')->with('current_category')
            ->willReturn($this->category);

        $this->collectionFilter->expects($this->once())->method('filter')
            ->with($this->collection, $this->category);

        $result = $this->model->prepareProductCollection($this->collection);
        $this->assertInstanceOf(Layer::class, $result);
    }

    public function testGetCurrentStore()
    {
        $this->assertInstanceOf(Store::class, $this->model->getCurrentStore());
    }

    public function testSetNewCurrentCategoryIfCurrentCategoryIsAnother()
    {
        $categoryId = 333;
        $currentCategoryId = 334;

        $this->category->method('getId')->willReturn($categoryId);
        $this->categoryRepository->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($this->currentCategory);

        $this->currentCategory->method('getId')->willReturn($currentCategoryId);
        $this->registry->expects($this->once())->method('registry')->with('current_category')
            ->willReturn($this->currentCategory);

        $this->assertInstanceOf(Layer::class, $this->model->setCurrentCategory($categoryId));
        $this->assertEquals($this->currentCategory, $this->model->getData('current_category'));
    }

    public function testSetNewCurrentCategoryIfCurrentCategoryIsSame()
    {
        $categoryId = 333;

        $this->category->method('getId')->willReturn($categoryId);

        $this->categoryRepository->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($this->category);
        $this->registry->expects($this->once())->method('registry')->with('current_category')
            ->willReturn($this->category);

        $this->assertInstanceOf(Layer::class, $this->model->setCurrentCategory($categoryId));
        $this->assertEquals($this->category, $this->model->getData('current_category'));
    }

    public function testSetNewCurrentCategoryIfCategoryIsNotFound()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Please correct the category.');
        $this->categoryRepository->expects($this->once())->method('get')
            ->willThrowException(new NoSuchEntityException());

        $this->model->setCurrentCategory(1);
    }

    public function testSetCurrentCategoryInstanceOfException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Must be category model instance or its id.');
        $this->model->setCurrentCategory(null);
    }

    public function testSetCurrentCategoryNotFoundException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Please correct the category.');
        $this->category->expects($this->once())->method('getId')->willReturn(null);

        $this->model->setCurrentCategory($this->category);
    }

    public function testGetCurrentCategory()
    {
        $this->currentCategory->getData('current_category', null);

        $this->registry->expects($this->once())->method('registry')->with('current_category')
            ->willReturn($this->currentCategory);

        $this->assertEquals($this->currentCategory, $this->model->getCurrentCategory());
        $this->assertEquals($this->currentCategory, $this->model->getData('current_category'));
    }

    public function testGetCurrentCategoryIfCurrentCategoryIsNotSet()
    {
        $rootCategoryId = 333;
        $this->currentCategory->getData('current_category', null);

        $this->registry->expects($this->once())->method('registry')->with('current_category')
            ->willReturn(null);
        $this->categoryRepository->expects($this->once())->method('get')->with($rootCategoryId)
            ->willReturn($this->currentCategory);
        $this->store->setRootCategoryId($rootCategoryId);

        $this->assertEquals($this->currentCategory, $this->model->getCurrentCategory());
        $this->assertEquals($this->currentCategory, $this->model->getData('current_category'));
    }
}
