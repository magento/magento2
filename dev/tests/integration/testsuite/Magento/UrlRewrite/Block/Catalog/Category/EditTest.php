<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Block\Catalog\Category;

use Magento\Catalog\Model\Category;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use Magento\UrlRewrite\Block\Catalog\Edit\Form;
use Magento\UrlRewrite\Block\Link;
use Magento\UrlRewrite\Block\Selector;
use Magento\UrlRewrite\Model\UrlRewrite;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\UrlRewrite\Block\Catalog\Category\Edit
 * @magentoAppArea adminhtml
 */
class EditTest extends TestCase
{
    /**
     * Test prepare layout
     *
     * @dataProvider prepareLayoutDataProvider
     *
     * @param array $blockAttributes
     * @param array $expected
     *
     * @return void
     * @magentoAppIsolation enabled
     */
    public function testPrepareLayout($blockAttributes, $expected): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $layoutFactory = $objectManager->get(LayoutFactory::class);
        /** @var $layout LayoutInterface */
        $layout = $layoutFactory->create();

        /** @var $block Edit */
        $block = $layout->createBlock(
            Edit::class,
            '',
            ['data' => $blockAttributes]
        );

        $this->checkSelector($block, $expected);
        $this->checkLinks($block, $expected);
        $this->checkButtons($block, $expected);
        $this->checkForm($block, $expected);
        $this->checkCategoriesTree($block, $expected);
    }

    /**
     * Check selector
     *
     * @param Edit $block
     * @param array $expected
     *
     * @return void
     */
    private function checkSelector($block, $expected): void
    {
        $layout = $block->getLayout();
        $blockName = $block->getNameInLayout();

        /** @var $selectorBlock Selector|bool */
        $selectorBlock = $layout->getChildBlock($blockName, 'selector');

        if ($expected['selector']) {
            $this->assertInstanceOf(
                Selector::class,
                $selectorBlock,
                'Child block with entity selector is invalid'
            );
        } else {
            $this->assertFalse($selectorBlock, 'Child block with entity selector should not present in block');
        }
    }

    /**
     * Check links
     *
     * @param Edit $block
     * @param array $expected
     *
     * @return void
     */
    private function checkLinks($block, $expected): void
    {
        $layout = $block->getLayout();
        $blockName = $block->getNameInLayout();

        /** @var $categoryBlock Link|bool */
        $categoryBlock = $layout->getChildBlock($blockName, 'category_link');

        if ($expected['category_link']) {
            $this->assertInstanceOf(
                Link::class,
                $categoryBlock,
                'Child block with category link is invalid'
            );

            $this->assertEquals(
                'Category:',
                $categoryBlock->getLabel(),
                'Child block with category has invalid item label'
            );

            $this->assertEquals(
                $expected['category_link']['name'],
                $categoryBlock->getItemName(),
                'Child block with category has invalid item name'
            );

            $this->assertMatchesRegularExpression(
                '/http:\/\/localhost\/index.php\/.*\/category/',
                $categoryBlock->getItemUrl(),
                'Child block with category contains invalid URL'
            );
        } else {
            $this->assertFalse($categoryBlock, 'Child block with category link should not present in block');
        }
    }

    /**
     * Check buttons
     *
     * @param Edit $block
     * @param array $expected
     *
     * @return void
     */
    private function checkButtons($block, $expected): void
    {
        $buttonsHtml = $block->getButtonsHtml();

        if (isset($expected['back_button'])) {
            if ($expected['back_button']) {
                if ($block->getCategory()->getId()) {
                    $this->assertMatchesRegularExpression(
                        '/setLocation\([\\\'\"]\S+?\/category/i',
                        $buttonsHtml,
                        'Back button is not present in category URL rewrite edit block'
                    );
                }
                $this->assertEquals(
                    1,
                    Xpath::getElementsCountForXpath(
                        '//button[contains(@class,"back")]',
                        $buttonsHtml
                    ),
                    'Back button is not present in category URL rewrite edit block'
                );
            } else {
                $this->assertEquals(
                    0,
                    Xpath::getElementsCountForXpath(
                        '//button[contains(@class,"back")]',
                        $buttonsHtml
                    ),
                    'Back button should not present in category URL rewrite edit block'
                );
            }
        }

        if ($expected['save_button']) {
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    '//button[contains(@class,"save")]',
                    $buttonsHtml
                ),
                'Save button is not present in category URL rewrite edit block'
            );
        } else {
            $this->assertEquals(
                0,
                Xpath::getElementsCountForXpath(
                    '//button[contains(@class,"save")]',
                    $buttonsHtml
                ),
                'Save button should not present in category URL rewrite edit block'
            );
        }

        if ($expected['reset_button']) {
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    '//button[@title="Reset"]',
                    $buttonsHtml
                ),
                'Reset button is not present in category URL rewrite edit block'
            );
        } else {
            $this->assertEquals(
                0,
                Xpath::getElementsCountForXpath(
                    '//button[@title="Reset"]',
                    $buttonsHtml
                ),
                'Reset button should not present in category URL rewrite edit block'
            );
        }

        if ($expected['delete_button']) {
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    '//button[contains(@class,"delete")]',
                    $buttonsHtml
                ),
                'Delete button is not present in category URL rewrite edit block'
            );
        } else {
            $this->assertEquals(
                0,
                Xpath::getElementsCountForXpath(
                    '//button[contains(@class,"delete")]',
                    $buttonsHtml
                ),
                'Delete button should not present in category URL rewrite edit block'
            );
        }
    }

    /**
     * Check form
     *
     * @param Edit $block
     * @param array $expected
     *
     * @return void
     */
    private function checkForm($block, $expected): void
    {
        $layout = $block->getLayout();
        $blockName = $block->getNameInLayout();

        /** @var $formBlock Form|bool */
        $formBlock = $layout->getChildBlock($blockName, 'form');

        if ($expected['form']) {
            $this->assertInstanceOf(
                Form::class,
                $formBlock,
                'Child block with form is invalid'
            );

            $this->assertSame(
                $expected['form']['category'],
                $formBlock->getCategory(),
                'Form block should have same category attribute'
            );

            $this->assertSame(
                $expected['form']['url_rewrite'],
                $formBlock->getUrlRewrite(),
                'Form block should have same URL rewrite attribute'
            );
        } else {
            $this->assertFalse($formBlock, 'Child block with form should not present in block');
        }
    }

    /**
     * Check categories tree
     *
     * @param Edit $block
     * @param array $expected
     *
     * @return void
     */
    private function checkCategoriesTree($block, $expected): void
    {
        $layout = $block->getLayout();
        $blockName = $block->getNameInLayout();

        /** @var $categoriesTreeBlock Tree|bool */
        $categoriesTreeBlock = $layout->getChildBlock($blockName, 'categories_tree');

        if ($expected['categories_tree']) {
            $this->assertInstanceOf(
                Tree::class,
                $categoriesTreeBlock,
                'Child block with categories tree is invalid'
            );
        } else {
            $this->assertFalse($categoriesTreeBlock, 'Child block with category_tree should not present in block');
        }
    }

    /**
     * Data provider
     *
     * @return array
     */
    public static function prepareLayoutDataProvider(): array
    {
        /** @var $urlRewrite UrlRewrite */
        $urlRewrite = Bootstrap::getObjectManager()->create(
            UrlRewrite::class
        );
        /** @var $category Category */
        $category = Bootstrap::getObjectManager()->create(
            Category::class,
            ['data' => ['entity_id' => 1, 'name' => 'Test category']]
        );
        /** @var $existingUrlRewrite UrlRewrite */
        $existingUrlRewrite = Bootstrap::getObjectManager()->create(
            UrlRewrite::class,
            ['data' => ['url_rewrite_id' => 1]]
        );

        return [
            // Creating URL rewrite when category selected
            [
                ['category' => $category, 'url_rewrite' => $urlRewrite],
                [
                    'selector' => false,
                    'category_link' => ['name' => $category->getName()],
                    'back_button' => true,
                    'save_button' => true,
                    'reset_button' => false,
                    'delete_button' => false,
                    'form' => ['category' => $category, 'url_rewrite' => $urlRewrite],
                    'categories_tree' => false
                ]
            ],
            // Creating URL rewrite when category not selected
            [
                ['url_rewrite' => $urlRewrite],
                [
                    'selector' => true,
                    'category_link' => false,
                    'back_button' => true,
                    'save_button' => false,
                    'reset_button' => false,
                    'delete_button' => false,
                    'form' => false,
                    'categories_tree' => true
                ]
            ],
            // Editing URL rewrite with category
            [
                ['url_rewrite' => $existingUrlRewrite, 'category' => $category],
                [
                    'selector' => false,
                    'category_link' => ['name' => $category->getName()],
                    'save_button' => true,
                    'reset_button' => true,
                    'delete_button' => true,
                    'form' => ['category' => $category, 'url_rewrite' => $existingUrlRewrite],
                    'categories_tree' => false
                ]
            ]
        ];
    }
}
