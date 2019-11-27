<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
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
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryFactory = $this->objectManager->create(CategoryFactory::class);
        $this->categoryRepository = $this->objectManager->create(CategoryRepositoryInterface::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Topmenu::class);
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
        $this->assertContains('Category 1', $output);
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
    public function invisibilityDataProvider(): array
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
            $this->assertContains(
                $data['name'],
                $output,
                'Category ' . $data['name'] . ' should appear in the menu!'
            );
        }
    }

    /**
     * @return array
     */
    public function categoriesVisibleInTreeProvider(): array
    {
        return [
            'add_in_tree_visible' => [
                'categories' => [
                    [
                        Category::KEY_NAME => 'Sub Category 1',
                        Category::KEY_PARENT_ID => 402,
                        Category::KEY_IS_ACTIVE => true,
                    ],
                ],
                'expectedCategories' => [
                    [
                        'is_contains' => true,
                        'name' => '>Sub Category 1<',
                    ],
                ],
            ],
            'child_visible_in_tree' => [
                'categories' => [
                    [
                        Category::KEY_NAME => 'Sub Category 1',
                        Category::KEY_PARENT_ID => 401,
                        Category::KEY_IS_ACTIVE => true,
                    ],
                    [
                        'id' => 401,
                        Category::KEY_IS_ACTIVE => false,
                    ],
                ],
                'expectedCategories' => [
                    [
                        'is_contains' => true,
                        'name' => '>Sub Category 1<',
                    ],
                    [
                        'is_contains' => true,
                        'name' => '>Category 1.1.1<',
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
            $this->assertNotContains(
                $data['name'],
                $output,
                'Category ' . $data['name'] . ' should not appear in the menu!'
            );
        }
    }

    /**
     * @return array
     */
    public function categoriesInTreeInvisibleProvider(): array
    {
        return [
            'add_in_tree_category_disable' => [
                'categories' => [
                    [
                        Category::KEY_NAME => 'Sub Category 1',
                        Category::KEY_PARENT_ID => 402,
                        Category::KEY_IS_ACTIVE => false,
                        Category::KEY_INCLUDE_IN_MENU => true,
                    ],
                ],
                'expectedCategories' => [
                    [
                        'is_contains' => false,
                        'name' => '>Sub Category 1<',
                    ],
                ],
            ],
            'add_in_tree_include_in_menu_disable' => [
                'categories' => [
                    [
                        Category::KEY_NAME => 'Sub Category 1',
                        Category::KEY_PARENT_ID => 402,
                        Category::KEY_IS_ACTIVE => true,
                        Category::KEY_INCLUDE_IN_MENU => false,
                    ],
                ],
                'expectedCategories' => [
                    [
                        'is_contains' => false,
                        'name' => '>Sub Category 1<',
                    ],
                ],
            ],
            'child_invisible_in_tree' => [
                'categories' => [
                    [
                        Category::KEY_NAME => 'Sub Category 1',
                        Category::KEY_PARENT_ID => 2,
                        Category::KEY_IS_ACTIVE => true,
                    ],
                    [
                        'id' => 400,
                        Category::KEY_IS_ACTIVE => false,
                    ],
                ],
                'expectedCategories' => [
                    [
                        'is_contains' => false,
                        'name' => '>Category 1.1<',
                    ],
                    [
                        'is_contains' => false,
                        'name' => '>Category 1.1.1<',
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
     * @magentoDbIsolation disabled
     * @param array $moveCategory
     * @param array $expectedMenuTree
     * @return void
     */
    public function testMenuStructure(array $moveCategory, array $expectedMenuTree): void
    {
        /** @var Category $category */
        $category = $this->categoryRepository->get($moveCategory['id']);
        $category->move($moveCategory['parent_id'], $moveCategory['after_category_id']);

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
    public function menuStructureProvider(): array
    {
        return [
            'move_to_default' => [
                'moveCategory' => [
                    'id' => 5,
                    'parent_id' => 2,
                    'after_category_id' => null,
                ],
                'expectedMenuTree' => [
                    5 => ['position' => '1'],
                    3 => [
                        'position' => '2',
                        4 => ['position' => '2-1'],
                    ],
                ],
            ],
            'move_to_not_default' => [
                'moveCategory' => [
                    'id' => 11,
                    'parent_id' => 10,
                    'after_category_id' => null,
                ],
                'expectedMenuTree' => [
                    10 => [
                        'position' => '5',
                        11 => ['position' => '5-1'],
                    ],
                    12 => ['position' => '6'],
                ],
            ],
            'move_tree_to_default' => [
                'moveCategory' => [
                    'id' => 4,
                    'parent_id' => 2,
                    'after_category_id' => null,
                ],
                'expectedMenuTree' => [
                    4 => [
                        'position' => '1',
                        5 => ['position' => '1-1'],
                    ],
                    3 => ['position' => '2'],
                ],
            ],
            'move_tree_to_other_tree' => [
                'moveCategory' => [
                    'id' => 14,
                    'parent_id' => 4,
                    'after_category_id' => 5,
                ],
                'expectedMenuTree' => [
                    3 => [
                        'position' => '1',
                        4 => [
                            'position' => '1-1',
                            5 => ['position' => '1-1-1'],
                            14 => [
                                'position' => '1-1-2',
                                15 => ['position' => '1-1-2-1'],
                            ],
                        ],
                    ],
                    6 => [
                        'position' => '2',
                        13 => ['position' => '2-1'],
                    ],
                ],
            ],
            'position_of_categories_in_default' => [
                'moveCategory' => [
                    'id' => 12,
                    'parent_id' => 2,
                    'after_category_id' => 7,
                ],
                'expectedMenuTree' => [
                    7 => ['position' => '3'],
                    12 => ['position' => '4'],
                    9 => ['position' => '5'],
                    10 => ['position' => '6'],
                    11 => ['position' => '7'],
                ],
            ],
            'position_of_categories_in_tree' => [
                'moveCategory' => [
                    'id' => 7,
                    'parent_id' => 6,
                    'after_category_id' => 13,
                ],
                'expectedMenuTree' => [
                    6 => [
                        'position' => '2',
                        13 => ['position' => '2-1'],
                        7 => ['position' => '2-2'],
                        14 => [
                            'position' => '2-3',
                            15 => ['position' => '2-3-1'],
                        ],
                    ],
                    9 => ['position' => '3'],
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
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testMultipleWebsitesCategoryDisplay(
        string $storeCode,
        string $expectedCategory,
        string $notExpectedCategory
    ): void {
        $this->storeManager->setCurrentStore($storeCode);
        $output = $this->block->getHtml('level-top', 'submenu', 0);
        $this->assertContains(
            $expectedCategory,
            $output,
            'Category "' . $expectedCategory . '" should appear in the menu!'
        );
        $this->assertNotContains(
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
    public function multipleWebsitesCategoryDisplayProvider(): array
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
            if (isset($categoryData['id'])) {
                $category = $this->categoryRepository->get($categoryData['id']);
                unset($categoryData['id']);
            } else {
                $category = $this->categoryFactory->create();
            }
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
            $id = str_replace('category-node-', '', $childNode->getId());
            $nodes[$id] = $this->getMenuTree($childNode);
        }

        return $nodes;
    }
}
