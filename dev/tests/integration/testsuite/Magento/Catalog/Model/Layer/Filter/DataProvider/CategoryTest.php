<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Layer\Filter\DataProvider;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class for test Category Data Provider
 *
 * @see \Magento\Catalog\Model\Layer\Filter\DataProvider\Category
 *
 * @magentoAppArea adminhtml
 */
class CategoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Category
     */
    private $provider;

    /**
     * @var CategoryInterfaceFactory
     */
    private $categoryFactory;

    /**
     * @var Registry
     */
    private $registry;

    /** @var Resolver */
    private $layerResolver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryFactory = $this->objectManager->get(CategoryInterfaceFactory::class);
        $this->layerResolver = $this->objectManager->get(Resolver::class);
        $this->provider = $this->objectManager->create(Category::class, ['layer' => $this->layerResolver->get()]);
        $this->registry = $this->objectManager->get(Registry::class);
    }

    /**
     * @return void
     */
    public function testValidateCategoryWithoutId(): void
    {
        $this->registry->register('current_category', $this->categoryFactory->create());
        $this->provider->setCategoryId(375211);
        $this->assertFalse($this->provider->isValid());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/inactive_category.php
     *
     * @return void
     */
    public function testValidateInactiveCategory(): void
    {
        $this->provider->setCategoryId(111);
        $this->assertFalse($this->provider->isValid());
    }

    /**
     * Data provider uses repository when loading category; same instance as controller load (no duplicate DB load).
     *
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoAppArea frontend
     *
     * @return void
     */
    public function testGetCategoryUsesRepositoryCacheWhenCategoryAlreadyLoaded(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var CategoryRepositoryInterface $categoryRepository */
        $categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $storeId = (int) $storeManager->getStore()->getId();
        $categoryId = 333;

        // Simulate controller: load category via repository (populates repository cache).
        $categoryFromRepository = $categoryRepository->get($categoryId, $storeId);
        $this->assertSame($categoryId, (int) $categoryFromRepository->getId());

        // Build layer with current category and create data provider (gets same repository from DI).
        $layer = $objectManager->create(
            \Magento\Catalog\Model\Layer\Category::class,
            ['data' => ['current_category' => $categoryFromRepository]]
        );
        $provider = $objectManager->create(Category::class, ['layer' => $layer]);
        $provider->setCategoryId($categoryId);

        // Data provider should return the same instance from repository cache (no second DB load).
        $categoryFromProvider = $provider->getCategory();
        $this->assertSame(
            $categoryFromRepository,
            $categoryFromProvider,
            'Data provider must return the same category instance from repository cache to avoid duplicate DB load'
        );
    }
}
