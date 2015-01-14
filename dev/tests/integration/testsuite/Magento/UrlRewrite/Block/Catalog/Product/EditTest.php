<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block\Catalog\Product;

/**
 * Test for \Magento\UrlRewrite\Block\Catalog\Product\Edit
 * @magentoAppArea adminhtml
 */
class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test prepare layout
     *
     * @dataProvider prepareLayoutDataProvider
     *
     * @param array $blockAttributes
     * @param array $expected
     *
     * @magentoAppIsolation enabled
     */
    public function testPrepareLayout($blockAttributes, $expected)
    {
        /** @var $layout \Magento\Framework\View\LayoutInterface */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );

        /** @var $block \Magento\UrlRewrite\Block\Catalog\Product\Edit */
        $block = $layout->createBlock(
            'Magento\UrlRewrite\Block\Catalog\Product\Edit',
            '',
            ['data' => $blockAttributes]
        );

        $this->_checkSelector($block, $expected);
        $this->_checkLinks($block, $expected);
        $this->_checkButtons($block, $expected);
        $this->_checkForm($block, $expected);
        $this->_checkGrid($block, $expected);
        $this->_checkCategories($block, $expected);
    }

    /**
     * Check selector
     *
     * @param \Magento\UrlRewrite\Block\Catalog\Product\Edit $block
     * @param array $expected
     */
    private function _checkSelector($block, $expected)
    {
        $layout = $block->getLayout();
        $blockName = $block->getNameInLayout();

        /** @var $selectorBlock \Magento\UrlRewrite\Block\Selector|bool */
        $selectorBlock = $layout->getChildBlock($blockName, 'selector');

        if ($expected['selector']) {
            $this->assertInstanceOf(
                'Magento\UrlRewrite\Block\Selector',
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
     * @param \Magento\UrlRewrite\Block\Catalog\Product\Edit $block
     * @param array $expected
     */
    private function _checkLinks($block, $expected)
    {
        $layout = $block->getLayout();
        $blockName = $block->getNameInLayout();

        /** @var $productLinkBlock \Magento\UrlRewrite\Block\Link|bool */
        $productLinkBlock = $layout->getChildBlock($blockName, 'product_link');

        if ($expected['product_link']) {
            $this->assertInstanceOf(
                'Magento\UrlRewrite\Block\Link',
                $productLinkBlock,
                'Child block with product link is invalid'
            );

            $this->assertEquals(
                'Product:',
                $productLinkBlock->getLabel(),
                'Child block with product link has invalid item label'
            );

            $this->assertEquals(
                $expected['product_link']['name'],
                $productLinkBlock->getItemName(),
                'Child block with product link has invalid item name'
            );

            $this->assertRegExp(
                '/http:\/\/localhost\/index.php\/.*\/product/',
                $productLinkBlock->getItemUrl(),
                'Child block with product link contains invalid URL'
            );
        } else {
            $this->assertFalse($productLinkBlock, 'Child block with product link should not present in block');
        }

        /** @var $categoryLinkBlock \Magento\UrlRewrite\Block\Link|bool */
        $categoryLinkBlock = $layout->getChildBlock($blockName, 'category_link');

        if ($expected['category_link']) {
            $this->assertInstanceOf(
                'Magento\UrlRewrite\Block\Link',
                $categoryLinkBlock,
                'Child block with category link is invalid'
            );

            $this->assertEquals(
                'Category:',
                $categoryLinkBlock->getLabel(),
                'Child block with category link has invalid item label'
            );

            $this->assertEquals(
                $expected['category_link']['name'],
                $categoryLinkBlock->getItemName(),
                'Child block with category link has invalid item name'
            );

            $this->assertRegExp(
                '/http:\/\/localhost\/index.php\/.*\/category/',
                $categoryLinkBlock->getItemUrl(),
                'Child block with category link contains invalid URL'
            );
        } else {
            $this->assertFalse($categoryLinkBlock, 'Child block with category link should not present in block');
        }
    }

    /**
     * Check buttons
     *
     * @param \Magento\UrlRewrite\Block\Catalog\Product\Edit $block
     * @param array $expected
     */
    private function _checkButtons($block, $expected)
    {
        $buttonsHtml = $block->getButtonsHtml();

        if (isset($expected['back_button'])) {
            if ($expected['back_button']) {
                if ($block->getProduct()->getId()) {
                    $this->assertSelectCount(
                        'button.back[onclick~="\/product"]',
                        1,
                        $buttonsHtml,
                        'Back button is not present in product URL rewrite edit block'
                    );
                } else {
                    $this->assertSelectCount(
                        'button.back',
                        1,
                        $buttonsHtml,
                        'Back button is not present in product URL rewrite edit block'
                    );
                }
            } else {
                $this->assertSelectCount(
                    'button.back',
                    0,
                    $buttonsHtml,
                    'Back button should not present in product URL rewrite edit block'
                );
            }
        }

        if ($expected['save_button']) {
            $this->assertSelectCount(
                'button.save',
                1,
                $buttonsHtml,
                'Save button is not present in product URL rewrite edit block'
            );
        } else {
            $this->assertSelectCount(
                'button.save',
                0,
                $buttonsHtml,
                'Save button should not present in product URL rewrite edit block'
            );
        }

        if ($expected['reset_button']) {
            $this->assertSelectCount(
                'button[title="Reset"]',
                1,
                $buttonsHtml,
                'Reset button is not present in product URL rewrite edit block'
            );
        } else {
            $this->assertSelectCount(
                'button[title="Reset"]',
                0,
                $buttonsHtml,
                'Reset button should not present in product URL rewrite edit block'
            );
        }

        if ($expected['delete_button']) {
            $this->assertSelectCount(
                'button.delete',
                1,
                $buttonsHtml,
                'Delete button is not present in product URL rewrite edit block'
            );
        } else {
            $this->assertSelectCount(
                'button.delete',
                0,
                $buttonsHtml,
                'Delete button should not present in product URL rewrite edit block'
            );
        }
    }

    /**
     * Check form
     *
     * @param \Magento\UrlRewrite\Block\Catalog\Product\Edit $block
     * @param array $expected
     */
    private function _checkForm($block, $expected)
    {
        $layout = $block->getLayout();
        $blockName = $block->getNameInLayout();

        /** @var $formBlock \Magento\UrlRewrite\Block\Catalog\Edit\Form|bool */
        $formBlock = $layout->getChildBlock($blockName, 'form');

        if ($expected['form']) {
            $this->assertInstanceOf(
                'Magento\UrlRewrite\Block\Catalog\Edit\Form',
                $formBlock,
                'Child block with form is invalid'
            );

            $this->assertSame(
                $block->getProduct(),
                $formBlock->getProduct(),
                'Form block should have same product attribute'
            );

            if ($block->getCategory()) {
                $this->assertSame(
                    $block->getCategory(),
                    $formBlock->getCategory(),
                    'Form block should have same category attribute'
                );
            }

            $this->assertSame(
                $block->getUrlRewrite(),
                $formBlock->getUrlRewrite(),
                'Form block should have same URL rewrite attribute'
            );
        } else {
            $this->assertFalse($formBlock, 'Child block with form should not present in block');
        }
    }

    /**
     * Check grid
     *
     * @param \Magento\UrlRewrite\Block\Catalog\Product\Edit $block
     * @param array $expected
     */
    private function _checkGrid($block, $expected)
    {
        $layout = $block->getLayout();
        $blockName = $block->getNameInLayout();

        /** @var $gridBlock \Magento\UrlRewrite\Block\Catalog\Product\Grid|bool */
        $gridBlock = $layout->getChildBlock($blockName, 'products_grid');

        if ($expected['products_grid']) {
            $this->assertInstanceOf(
                'Magento\UrlRewrite\Block\Catalog\Product\Grid',
                $gridBlock,
                'Child block with product grid is invalid'
            );
        } else {
            $this->assertFalse($gridBlock, 'Child block with product grid should not present in block');
        }
    }

    /**
     * Check categories
     *
     * @param \Magento\UrlRewrite\Block\Catalog\Product\Edit $block
     * @param array $expected
     */
    private function _checkCategories($block, $expected)
    {
        $layout = $block->getLayout();
        $blockName = $block->getNameInLayout();

        /** @var $categoriesTreeBlock \Magento\UrlRewrite\Block\Catalog\Category\Tree|bool */
        $categoriesTreeBlock = $layout->getChildBlock($blockName, 'categories_tree');

        if ($expected['categories_tree']) {
            $this->assertInstanceOf(
                'Magento\UrlRewrite\Block\Catalog\Category\Tree',
                $categoriesTreeBlock,
                'Child block with categories tree is invalid'
            );
        } else {
            $this->assertFalse($categoriesTreeBlock, 'Child block with categories tree should not present in block');
        }

        /** @var $skipCategoriesBlock \Magento\Backend\Block\Widget\Button|bool */
        $skipCategoriesBlock = $layout->getChildBlock($blockName, 'skip_categories');

        if ($expected['skip_categories']) {
            $this->assertInstanceOf(
                'Magento\Backend\Block\Widget\Button',
                $skipCategoriesBlock,
                'Child block with skip categories is invalid'
            );
        } else {
            $this->assertFalse($skipCategoriesBlock, 'Child block with skip categories should not present in block');
        }
    }

    /**
     * Data provider
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function prepareLayoutDataProvider()
    {
        /** @var $urlRewrite \Magento\UrlRewrite\Model\UrlRewrite */
        $urlRewrite = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\UrlRewrite\Model\UrlRewrite'
        );
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product',
            ['data' => ['entity_id' => 1, 'name' => 'Test product']]
        );
        /** @var $category \Magento\Catalog\Model\Category */
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Category',
            ['data' => ['entity_id' => 1, 'name' => 'Test category']]
        );
        /** @var $existingUrlRewrite \Magento\UrlRewrite\Model\UrlRewrite */
        $existingUrlRewrite = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\UrlRewrite\Model\UrlRewrite',
            ['data' => ['url_rewrite_id' => 1]]
        );
        return [
            [ // Creating URL rewrite when product and category are not selected
                ['url_rewrite' => $urlRewrite],
                [
                    'selector' => true,
                    'product_link' => false,
                    'category_link' => false,
                    'back_button' => true,
                    'reset_button' => false,
                    'delete_button' => false,
                    'save_button' => false,
                    'form' => false,
                    'products_grid' => true,
                    'categories_tree' => false,
                    'skip_categories' => false
                ],
            ],
            [ // Creating URL rewrite when product selected and category tree active
                ['product' => $product, 'url_rewrite' => $urlRewrite, 'is_category_mode' => true],
                [
                    'selector' => false,
                    'product_link' => ['name' => $product->getName()],
                    'category_link' => false,
                    'back_button' => true,
                    'reset_button' => false,
                    'delete_button' => false,
                    'save_button' => false,
                    'form' => false,
                    'products_grid' => false,
                    'categories_tree' => true,
                    'skip_categories' => true
                ]
            ],
            [ // Creating URL rewrite when product selected and category tree inactive
                ['product' => $product, 'url_rewrite' => $urlRewrite],
                [
                    'selector' => false,
                    'product_link' => ['name' => $product->getName()],
                    'category_link' => false,
                    'back_button' => true,
                    'reset_button' => false,
                    'delete_button' => false,
                    'save_button' => true,
                    'form' => true,
                    'products_grid' => false,
                    'categories_tree' => false,
                    'skip_categories' => false
                ]
            ],
            [ // Creating URL rewrite when product selected and category selected
                ['product' => $product, 'category' => $category, 'url_rewrite' => $urlRewrite],
                [
                    'selector' => false,
                    'product_link' => ['name' => $product->getName()],
                    'category_link' => ['name' => $category->getName()],
                    'back_button' => true,
                    'reset_button' => false,
                    'delete_button' => false,
                    'save_button' => true,
                    'form' => true,
                    'products_grid' => false,
                    'categories_tree' => false,
                    'skip_categories' => false
                ]
            ],
            [ // Editing existing URL rewrite with product and category
                ['product' => $product, 'category' => $category, 'url_rewrite' => $existingUrlRewrite],
                [
                    'selector' => false,
                    'product_link' => ['name' => $product->getName()],
                    'category_link' => ['name' => $category->getName()],
                    'reset_button' => true,
                    'delete_button' => true,
                    'save_button' => true,
                    'form' => true,
                    'products_grid' => false,
                    'categories_tree' => false,
                    'skip_categories' => false
                ]
            ]
        ];
    }
}
