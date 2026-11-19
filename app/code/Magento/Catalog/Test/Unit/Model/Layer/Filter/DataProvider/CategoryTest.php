<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Layer\Filter\DataProvider;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Category as CategoryDataProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Catalog\Model\Layer\Filter\DataProvider\Category
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryTest extends TestCase
{
    /** @var  Category|MockObject */
    private $category;

    /** @var  Store|MockObject */
    private $store;

    /** @var  Layer|MockObject */
    private $layer;

    /** @var  CategoryFactory|MockObject */
    private $categoryFactory;

    /** @var  Registry|MockObject */
    private $coreRegistry;

    /**
     * Repository served by the application object manager when none is injected
     *
     * @var CategoryRepositoryInterface|MockObject
     */
    private $categoryRepository;

    /** @var ObjectManagerInterface|null */
    private $originalObjectManager;

    /**
     * Data provider constructed the legacy way, with three arguments and no repository
     *
     * @var CategoryDataProvider
     */
    private $target;

    protected function setUp(): void
    {
        $this->coreRegistry = $this->createPartialMock(Registry::class, ['register']);
        $this->category = $this->createPartialMock(Category::class, ['getId', 'getPathIds']);
        $this->categoryFactory = $this->createPartialMock(CategoryFactory::class, ['create']);
        $this->categoryFactory->expects($this->never())->method('create');
        $this->store = $this->createPartialMock(Store::class, ['getId']);
        $this->layer = $this->createPartialMock(Layer::class, ['getCurrentStore', 'getCurrentCategory']);
        $this->layer->method('getCurrentStore')->willReturn($this->store);
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);

        try {
            $this->originalObjectManager = ObjectManager::getInstance();
        } catch (\RuntimeException $e) {
            $this->originalObjectManager = null;
        }
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->method('get')->willReturnCallback(
            fn (string $type) => $type === CategoryRepositoryInterface::class ? $this->categoryRepository : null
        );
        ObjectManager::setInstance($objectManager);

        $this->target = new CategoryDataProvider($this->coreRegistry, $this->categoryFactory, $this->layer);
    }

    protected function tearDown(): void
    {
        if ($this->originalObjectManager !== null) {
            ObjectManager::setInstance($this->originalObjectManager);
        }
    }

    /**
     * Legacy three-argument construction: the repository comes from the object manager, the factory is not used.
     *
     * @return CategoryDataProvider
     */
    public function testGetCategoryWithAppliedId()
    {
        $storeId = 1234;
        $categoryId = 4321;
        $this->store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->layer->method('getCurrentCategory')->willReturn($this->category);
        $this->categoryRepository->expects($this->once())
            ->method('get')
            ->with($categoryId, $storeId)
            ->willReturn($this->category);
        $this->category->method('getId')->willReturn($categoryId);
        $this->category->method('getPathIds')->willReturn([20, 10]);
        $this->coreRegistry->expects($this->once())
            ->method('register')
            ->with(
                'current_category_filter',
                $this->category,
                true
            )->willReturnSelf();
        $this->target->setCategoryId($categoryId);
        $this->assertSame($this->category, $this->target->getCategory());
        $this->assertSame($this->category, $this->target->getCategory(), 'Second call must reuse the category');
        $this->assertSame(20, $this->target->getResetValue());

        return $this->target;
    }

    /**
     * An injected repository takes precedence over the one from the object manager.
     */
    public function testGetCategoryUsesInjectedRepository(): void
    {
        $storeId = 1234;
        $categoryId = 4321;
        $this->store->method('getId')->willReturn($storeId);
        $this->layer->method('getCurrentCategory')->willReturn($this->category);
        $this->category->method('getId')->willReturn($categoryId);
        $this->coreRegistry->method('register')->willReturnSelf();
        $this->categoryRepository->expects($this->never())->method('get');

        $injectedRepository = $this->createMock(CategoryRepositoryInterface::class);
        $injectedRepository->expects($this->once())
            ->method('get')
            ->with($categoryId, $storeId)
            ->willReturn($this->category);

        $target = new CategoryDataProvider(
            $this->coreRegistry,
            $this->categoryFactory,
            $this->layer,
            $injectedRepository
        );
        $target->setCategoryId($categoryId);

        $this->assertSame($this->category, $target->getCategory());
    }

    /**
     * When the repository cannot find the category, the layer's current category is used instead.
     */
    public function testGetCategoryFallsBackToCurrentCategoryWhenRepositoryThrowsNoSuchEntityException(): void
    {
        $storeId = 1234;
        $categoryId = 4321;
        $currentCategory = $this->createPartialMock(Category::class, ['getId', 'getPathIds']);
        $currentCategory->method('getId')->willReturn(999);
        $this->store->method('getId')->willReturn($storeId);
        $this->layer->method('getCurrentCategory')->willReturn($currentCategory);
        $this->coreRegistry->expects($this->once())
            ->method('register')
            ->with('current_category_filter', $currentCategory, true)
            ->willReturnSelf();
        $this->categoryRepository->expects($this->once())
            ->method('get')
            ->with($categoryId, $storeId)
            ->willThrowException(new NoSuchEntityException(__('Category not found')));

        $this->target->setCategoryId($categoryId);

        $this->assertSame($currentCategory, $this->target->getCategory());
    }

    /**
     * A non-numeric id (e.g. "cat[]=5" in the request) never reaches the repository; the current category is used.
     */
    public function testGetCategoryFallsBackToCurrentCategoryForNonNumericId(): void
    {
        $currentCategory = $this->createPartialMock(Category::class, ['getId', 'getPathIds']);
        $currentCategory->method('getId')->willReturn(999);
        $this->layer->method('getCurrentCategory')->willReturn($currentCategory);
        $this->coreRegistry->method('register')->willReturnSelf();
        $this->categoryRepository->expects($this->never())->method('get');

        $this->target->setCategoryId(['5']);

        $this->assertSame($currentCategory, $this->target->getCategory());
    }

    /**
     * The public contract stays backward compatible: optional repository argument, untyped getCategory().
     */
    public function testPublicContractIsBackwardCompatible(): void
    {
        $constructorParameters = (new \ReflectionMethod(CategoryDataProvider::class, '__construct'))->getParameters();
        $repositoryParameter = end($constructorParameters);

        $this->assertSame('categoryRepository', $repositoryParameter->getName());
        $this->assertTrue($repositoryParameter->isOptional(), 'Three-argument callers must keep working');
        $this->assertTrue($repositoryParameter->allowsNull());
        $this->assertFalse(
            (new \ReflectionMethod(CategoryDataProvider::class, 'getCategory'))->hasReturnType(),
            'Existing untyped overrides of getCategory() must stay compatible'
        );
    }
}
