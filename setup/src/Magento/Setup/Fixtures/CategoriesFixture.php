<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

/**
 * Generate categories.
 * Support the following format:
 * <categories>{amount of categories}</categories>
 * <categories_nesting_level>{Nesting level of categories}</categories_nesting_level>
 *
 * If config "assign_entities_to_all_websites" set to "0" then all categories will be
 * uniformly distributed per root categories, else all categories assigned to one root category
 */
class CategoriesFixture extends Fixture
{
    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var int
     */
    private $firstLevelCategoryIndex;

    /**
     * @var array
     */
    private $rootCategoriesIds;

    /**
     * @var int
     */
    private $categoriesNumber;

    /**
     * @var int
     */
    private $maxNestingLevel;

    /**
     * CategoriesFixture constructor.
     * @param FixtureModel $fixtureModel
     * @param StoreManager $storeManager
     * @param CategoryFactory $categoryFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        FixtureModel $fixtureModel,
        StoreManager $storeManager,
        CategoryFactory $categoryFactory,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($fixtureModel);
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @var int
     */
    protected $priority = 20;

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->categoriesNumber = $this->getCategoriesAmount();
        if (!$this->categoriesNumber) {
            return;
        }
        $this->maxNestingLevel = $this->fixtureModel->getValue('categories_nesting_level', 3);

        $categoriesNumberOnLevel = abs(ceil(pow($this->categoriesNumber, 1 / $this->maxNestingLevel) - 2));
        foreach ($this->getRootCategoriesIds() as $parentCategoryId) {
            $category = $this->categoryFactory->create();
            $category->load($parentCategoryId);
            // Need for generation url rewrites per all category store view
            $category->setStoreId(Store::DEFAULT_STORE_ID);
            $categoryIndex = 1;
            $this->generateCategories(
                $category,
                $categoriesNumberOnLevel,
                1,
                $categoryIndex
            );
        }
    }

    /**
     * Generate categories
     *
     * @param Category $parentCategory
     * @param int $categoriesNumberOnLevel
     * @param int $nestingLevel
     * @param int $categoryIndex
     * @return void
     */
    private function generateCategories(
        Category $parentCategory,
        $categoriesNumberOnLevel,
        $nestingLevel,
        &$categoryIndex
    ) {
        $maxCategoriesNumberOnLevel = $nestingLevel === 1 ? $this->categoriesNumber : $categoriesNumberOnLevel;
        for ($i = 0; $i < $maxCategoriesNumberOnLevel && $categoryIndex <= $this->categoriesNumber; $i++) {
            try {
                $category = clone $parentCategory;
                $category->setId(null)
                    ->setUrlKey(null)
                    ->setUrlPath(null)
                    ->setStoreId(Store::DEFAULT_STORE_ID)
                    ->setName($this->getCategoryName($parentCategory, $nestingLevel, $i))
                    ->setParentId($parentCategory->getId())
                    ->setLevel($parentCategory->getLevel() + 1)
                    ->setAvailableSortBy('name')
                    ->setIsAnchor($nestingLevel <= 2)
                    ->setDefaultSortBy('name')
                    ->setIsActive(true);
                $category->save();
                $categoryIndex++;
                if ($nestingLevel < $this->maxNestingLevel) {
                    $this->generateCategories(
                        $category,
                        $categoriesNumberOnLevel,
                        $nestingLevel + 1,
                        $categoryIndex
                    );
                }
            } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
                $categoryIndex++;
                continue;
            } catch (\Magento\Framework\DB\Adapter\DuplicateException $e) {
                $categoryIndex++;
                continue;
            }
        }
    }

    /**
     * Get category name based on parent category and current level
     *
     * @param Category $parentCategory
     * @param int $nestingLevel
     * @param int $index
     * @return string
     */
    private function getCategoryName($parentCategory, $nestingLevel, $index)
    {
        $categoryNameSuffix = $nestingLevel === 1 ? $this->getFirstLevelCategoryIndex() + $index : $index + 1;
        return ($nestingLevel === 1 ? $this->getCategoryPrefix() . ' ' : $parentCategory->getName() . '.')
            . $categoryNameSuffix;
    }

    /**
     * Get ids of root categories
     *
     * @return int[]
     */
    private function getRootCategoriesIds()
    {
        if (null === $this->rootCategoriesIds) {
            $this->rootCategoriesIds = [];
            foreach ($this->storeManager->getGroups() as $storeGroup) {
                $this->rootCategoriesIds[] = $storeGroup->getRootCategoryId();
                // in this case root category will be the same for all store groups
                if ((bool)$this->fixtureModel->getValue('assign_entities_to_all_websites', false)) {
                    break;
                }
            }
        }

        return $this->rootCategoriesIds;
    }

    /**
     * Get categories amount for generation
     *
     * @return int
     */
    private function getCategoriesAmount()
    {
        $categoriesAmount = $this->collectionFactory->create()->getSize();
        $rootCategories = count($this->getRootCategoriesIds());
        $categoriesNumber = $this->fixtureModel->getValue('categories', 0) - ($categoriesAmount - $rootCategories - 1);

        return max(
            0,
            ceil($categoriesNumber / $rootCategories)
        );
    }

    /**
     * Get next category index, which will be used as index of first-level category
     *
     * @return int
     */
    private function getFirstLevelCategoryIndex()
    {
        if (null === $this->firstLevelCategoryIndex) {
            $this->firstLevelCategoryIndex = $this->collectionFactory->create()
                    ->addFieldToFilter('level', 2)
                    ->getSize() + 1;
        }

        return $this->firstLevelCategoryIndex;
    }

    /**
     * Get Category name prefix
     *
     * @return string
     */
    private function getCategoryPrefix()
    {
        return 'Category';
    }

    /**
     * @inheritdoc
     */
    public function getActionTitle()
    {
        return 'Generating categories';
    }

    /**
     * @inheritdoc
     */
    public function introduceParamLabels()
    {
        return [
            'categories' => 'Categories'
        ];
    }
}
