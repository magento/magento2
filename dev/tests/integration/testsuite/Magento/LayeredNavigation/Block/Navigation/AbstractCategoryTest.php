<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\LayeredNavigation\Block\Navigation;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Base class for filters block tests on category page.
 */
abstract class AbstractCategoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var CategoryResource
     */
    protected $categoryResource;

    /**
     * @var Navigation
     */
    protected $navigationBlock;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryCollectionFactory = $this->objectManager->create(CollectionFactory::class);
        $this->categoryResource = $this->objectManager->get(CategoryResource::class);
        $this->layout = $this->objectManager->create(LayoutInterface::class);
        $this->navigationBlock = $this->objectManager->create(Category::class);
        parent::setUp();
    }

    /**
     * Inits navigation block.
     *
     * @param string $categoryName
     * @param int $storeId
     * @return void
     */
    protected function prepareNavigationBlock(
        string $categoryName,
        int $storeId = Store::DEFAULT_STORE_ID
    ): void {
        $category = $this->loadCategory($categoryName, $storeId);
        $this->navigationBlock->getLayer()->setCurrentCategory($category);
        $this->navigationBlock->setLayout($this->layout);
    }

    /**
     * Loads category by id.
     *
     * @param string $categoryName
     * @param int $storeId
     * @return CategoryInterface
     */
    protected function loadCategory(string $categoryName, int $storeId): CategoryInterface
    {
        /** @var Collection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create();
        /** @var CategoryInterface $category */
        $category = $categoryCollection->setStoreId($storeId)
            ->addAttributeToSelect('display_mode', 'left')
            ->addAttributeToFilter(CategoryInterface::KEY_NAME, $categoryName)
            ->setPageSize(1)
            ->getFirstItem();
        $category->setStoreId($storeId);

        return $category;
    }
}
