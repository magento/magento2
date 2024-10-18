<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Theme\Block\Html\Topmenu;
use PHPUnit\Framework\TestCase;

/**
 * Class checks top menu link behaviour.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class TopMenuTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var LayoutInterface */
    private $layout;

    /** @var Topmenu */
    private $block;

    /** @var CategoryFactory */
    private $categoryFactory;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryFactory = $this->objectManager->get(CategoryFactory::class);
        $this->categoryRepository = $this->objectManager->create(CategoryRepositoryInterface::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(Topmenu::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * Checks menu item displaying.
     *
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @return void
     */
    public function testTopMenuItemDisplay(): void
    {
        $output = $this->block->getHtml('level-top', 'submenu', 0);
        $this->assertStringContainsString('Category 1', $output);
    }

    /**
     * Checks that menu item is not displayed if the category is disabled or include in menu is disabled.
     *
     * @dataProvider invisibilityDataProvider
     * @param array $data
     * @return void
     */
    public function testTopMenuItemInvisibility(array $data): void
    {
        $category = $this->categoryFactory->create();
        $category->setData($data);
        $this->categoryRepository->save($category);
        $output = $this->block->getHtml('level-top', 'submenu', 0);
        $this->assertEmpty($output, 'The category is displayed in top menu navigation');
    }

    /**
     * @return array
     */
    public static function invisibilityDataProvider(): array
    {
        return [
            'include_in_menu_disable' => [
                'data' => [
                    'name' => 'Test Category',
                    'path' => '1/2/',
                    'is_active' => '1',
                    'include_in_menu' => false,
                ],
            ],
            'category_disable' => [
                'data' => [
                    'name' => 'Test Category 2',
                    'path' => '1/2/',
                    'is_active' => false,
                    'include_in_menu' => true,
                ],
            ],
        ];
    }

    /**
     * Check category visibility in the category tree in the menu
     *
     * @dataProvider categoriesVisibleInTreeProvider
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @magentoAppIsolation enabled
     * @param array $categories
     * @param array $expectedCategories
     * @return void
     */
    public function testCategoriesInTreeVisible(array $categories, array $expectedCategories): void
    {
        $this->updateCategories($categories);
        $output = $this->block->getHtml('level-top', 'submenu', 0);
        foreach ($expectedCategories as $data) {
            $this->assertStringContainsString(
                $data['name'],
                $output,
                'Category ' . $data['name'] . ' should appear in the menu!'
            );
        }
    }

    /**
     * @return array
     */
    public static function categoriesVisibleInTreeProvider(): array
    {
        return [
            'add_in_tree_visible' => [
                'categories' => [
                    [
                        'is_new_category' => true,
                        'parent_name' => 'Category 1.1.1',
                        Category::KEY_NAME => 'Sub Category 1',
                        Category::KEY_IS_ACTIVE => true,
                    ],
                ],
                'expectedCategories' => [
                    [
                        'name' => 'Sub Category 1',
                    ],
                ],
            ],
            'child_visible_in_tree' => [
                'categories' => [
                    [
                        'is_new_category' => true,
                        'parent_name' => 'Category 1.1',
                        Category::KEY_NAME => 'Sub Category 1',
                        Category::KEY_IS_ACTIVE => true,
                    ],
                    [
                        'is_new_category' => false,
                        'category_name' => 'Category 1.1',
                        Category::KEY_IS_ACTIVE => false,
                    ],
                ],
                'expectedCategories' => [
                    [
                        'name' => 'Sub Category 1',
                    ],
                    [
                        'name' => 'Category 1.1.1',
                    ],
                ],
            ],
        ];
    }

    /**
     * Check invisibility of a category in the category tree in the menu
     *
     * @dataProvider categoriesInTreeInvisibleProvider
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @magentoAppIsolation enabled
     * @param array $categories
     * @param array $expectedCategories
     * @return void
     */
    public function testCategoriesInTreeInvisible(array $categories, array $expectedCategories): void
    {
        $this->updateCategories($categories);
        $output = $this->block->getHtml('level-top', 'submenu', 0);
        foreach ($expectedCategories as $data) {
            $this->assertStringNotContainsString(
                $data['name'],
                $output,
                'Category ' . $data['name'] . ' should not appear in the menu!'
            );
        }
    }

    /**
     * @return array
     */
    public static function categoriesInTreeInvisibleProvider(): array
    {
        return [
            'add_in_tree_category_disable' => [
                'categories' => [
                    [
                        'is_new_category' => true,
                        'parent_name' => 'Category 1.1.1',
                        Category::KEY_NAME => 'Sub Category 1',
                        Category::KEY_IS_ACTIVE => false,
                        Category::KEY_INCLUDE_IN_MENU => true,
                    ],
                ],
                'expectedCategories' => [
                    [
                        'name' => 'Sub Category 1',
                    ],
                ],
            ],
            'add_in_tree_include_in_menu_disable' => [
                'categories' => [
                    [
                        'is_new_category' => true,
                        'parent_name' => 'Category 1.1.1',
                        Category::KEY_NAME => 'Sub Category 1',
                        Category::KEY_IS_ACTIVE => true,
                        Category::KEY_INCLUDE_IN_MENU => false,
                    ],
                ],
                'expectedCategories' => [
                    [
                        'name' => 'Sub Category 1',
                    ],
                ],
            ],
            'child_invisible_in_tree' => [
                'categories' => [
                    [
                        'is_new_category' => true,
                        'parent_name' => 'Default Category',
                        Category::KEY_NAME => 'Sub Category 1',
                        Category::KEY_IS_ACTIVE => true,
                    ],
                    [
                        'is_new_category' => false,
                        'category_name' => 'Category 1',
                        Category::KEY_IS_ACTIVE => false,
                    ],
                ],
                'expectedCategories' => [
                    [
                        'name' => 'Category 1.1',
                    ],
                    [
                        'name' => 'Category 1.1.1',
                    ],
                ],
            ],
        ];
    }

    /**
     * Check menu structure after moving category or changing position
     *
     * @dataProvider menuStructureProvider
     * @magentoDataFixture Magento/Catalog/_files/categories_no_products_with_two_tree.php
     * @magentoAppIsolation enabled
     * @param array $moveCategory
     * @param array $expectedMenuTree
     * @return void
     */
    public function testMenuStructure(array $moveCategory, array $expectedMenuTree): void
    {
        /** @var Category $category */
        $category = $this->categoryRepository->get($this->getCategoryIdByName($moveCategory['name']));
        $category->move(
            $this->getCategoryIdByName($moveCategory['parent_name']),
            $this->getCategoryIdByName($moveCategory['after_category_name'])
        );

        $this->block->getHtml('level-top', 'submenu', 0);

        $menuTree = $this->getMenuTree($this->block->getMenu());
        $topLevelKeys = array_flip(array_keys($expectedMenuTree));
        $actualMenuTree = array_intersect_key($menuTree, $topLevelKeys);
        $this->assertEquals(
            $expectedMenuTree,
            $actualMenuTree,
            'Error in displaying the menu tree after moving a category!'
        );
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function menuStructureProvider(): array
    {
        return [
            'move_to_default' => [
                'moveCategory' => [
                    'name' => 'Category 1.1.1',
                    'parent_name' => 'Default Category',
                    'after_category_name' => '',
                ],
                'expectedMenuTree' => [
                    'Category 1.1.1' => ['position' => '1'],
                    'Category 1' => [
                        'position' => '2',
                        'Category 1.1' => ['position' => '2-1'],
                    ],
                ],
            ],
            'move_to_not_default' => [
                'moveCategory' => [
                    'name' => 'Movable Position 3',
                    'parent_name' => 'Movable Position 2',
                    'after_category_name' => '',
                ],
                'expectedMenuTree' => [
                    'Movable Position 2' => [
                        'position' => '5',
                        'Movable Position 3' => ['position' => '5-1'],
                    ],
                    'Category 12' => ['position' => '6'],
                ],
            ],
            'move_tree_to_default' => [
                'moveCategory' => [
                    'name' => 'Category 1.1',
                    'parent_name' => 'Default Category',
                    'after_category_name' => '',
                ],
                'expectedMenuTree' => [
                    'Category 1.1' => [
                        'position' => '1',
                        'Category 1.1.1' => ['position' => '1-1'],
                    ],
                    'Category 1' => ['position' => '2'],
                ],
            ],
            'move_tree_to_other_tree' => [
                'moveCategory' => [
                    'name' => 'Category 2.2',
                    'parent_name' => 'Category 1.1',
                    'after_category_name' => 'Category 1.1.1',
                ],
                'expectedMenuTree' => [
                    'Category 1' => [
                        'position' => '1',
                        'Category 1.1' => [
                            'position' => '1-1',
                            'Category 1.1.1' => ['position' => '1-1-1'],
                            'Category 2.2' => [
                                'position' => '1-1-2',
                                'Category 2.2.1' => ['position' => '1-1-2-1'],
                            ],
                        ],
                    ],
                    'Category 2' => [
                        'position' => '2',
                        'Category 2.1' => ['position' => '2-1'],
                    ],
                ],
            ],
            'position_of_categories_in_default' => [
                'moveCategory' => [
                    'name' => 'Category 12',
                    'parent_name' => 'Default Category',
                    'after_category_name' => 'Movable',
                ],
                'expectedMenuTree' => [
                    'Movable' => ['position' => '3'],
                    'Category 12' => ['position' => '4'],
                    'Movable Position 1' => ['position' => '5'],
                    'Movable Position 2' => ['position' => '6'],
                    'Movable Position 3' => ['position' => '7'],
                ],
            ],
            'position_of_categories_in_tree' => [
                'moveCategory' => [
                    'name' => 'Movable',
                    'parent_name' => 'Category 2',
                    'after_category_name' => 'Category 2.1',
                ],
                'expectedMenuTree' => [
                    'Category 2' => [
                        'position' => '2',
                        'Category 2.1' => ['position' => '2-1'],
                        'Movable' => ['position' => '2-2'],
                        'Category 2.2' => [
                            'position' => '2-3',
                            'Category 2.2.1' => ['position' => '2-3-1'],
                        ],
                    ],
                    'Movable Position 1' => ['position' => '3'],
                ],
            ],
        ];
    }

    /**
     * Test the display of category in menu on different websites
     *
     * @dataProvider multipleWebsitesCategoryDisplayProvider
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoDataFixture Magento/Catalog/_files/category_in_second_root_category.php
     * @param string $storeCode
     * @param string $expectedCategory
     * @param string $notExpectedCategory
     * @return void
     */
    public function testMultipleWebsitesCategoryDisplay(
        string $storeCode,
        string $expectedCategory,
        string $notExpectedCategory
    ): void {
        $this->storeManager->setCurrentStore($storeCode);
        $output = $this->block->getHtml('level-top', 'submenu', 0);
        $this->assertStringContainsString(
            $expectedCategory,
            $output,
            'Category "' . $expectedCategory . '" should appear in the menu!'
        );
        $this->assertStringNotContainsString(
            $notExpectedCategory,
            $output,
            'Category "' . $notExpectedCategory . '" should not appear in the menu!'
        );
    }

    /**
     * Provide test data to verify the display of category in menu on different websites.
     *
     * @return array
     */
    public static function multipleWebsitesCategoryDisplayProvider(): array
    {
        return [
            'first_website' => [
                'storeCode' => 'default',
                'expectedCategory' => '>Category 1<',
                'notExpectedCategory' => '>Root2 Category 1<',
            ],
            'second_website' => [
                'storeCode' => 'test_store_1',
                'expectedCategory' => '>Root2 Category 1<',
                'notExpectedCategory' => '>Category 1<',
            ],
        ];
    }

    /**
     * Update existing categories or create new ones
     *
     * @param array $categories
     * @return void
     */
    private function updateCategories(array $categories): void
    {
        foreach ($categories as $categoryData) {
            if (!$categoryData['is_new_category']) {
                $category = $this->categoryRepository->get($this->getCategoryIdByName($categoryData['category_name']));
                unset($categoryData['category_name']);
            } else {
                $categoryData[Category::KEY_PARENT_ID] = $this->getCategoryIdByName($categoryData['parent_name']);
                unset($categoryData['parent_name']);
                $category = $this->categoryFactory->create();
            }
            unset($categoryData['is_new_category']);
            $category->addData($categoryData);
            $this->categoryRepository->save($category);
        }
    }

    /**
     * Get an array from the menu tree with category identifiers and their position
     *
     * @param Node $node
     * @return array
     */
    private function getMenuTree(Node $node): array
    {
        $nodes = [];
        if (!is_null($node->getId())) {
            $nodes['position'] = str_replace('nav-', '', $node->getData('position_class'));
        }
        $childrenNodes = $node->getChildren()->getNodes();
        /** @var Node $childNode */
        foreach ($childrenNodes as $childNode) {
            $name = $childNode->getName();
            $nodes[$name] = $this->getMenuTree($childNode);
        }

        return $nodes;
    }

    /**
     * @param string $name
     * @return string|null
     */
    private function getCategoryIdByName(string $name): ?string
    {
        $categoryCollectionFactory = $this->objectManager->get(CollectionFactory::class);
        /** @var Collection $categoryCollection */
        $categoryCollection = $categoryCollectionFactory->create();
        /** @var $category Category */
        $category = $categoryCollection
            ->addAttributeToFilter(CategoryInterface::KEY_NAME, $name)
            ->setPageSize(1)
            ->getFirstItem();

        return $category->getId();
    }
}
