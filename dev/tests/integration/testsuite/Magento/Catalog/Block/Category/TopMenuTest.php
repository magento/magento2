<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
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

    /** @var Topmenu */
    private $block;

    /** @var CategoryFactory */
    private $categoryFactory;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryFactory = $this->objectManager->create(CategoryFactory::class);
        $this->categoryRepository = $this->objectManager->create(CategoryRepositoryInterface::class);
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Topmenu::class);
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
}
