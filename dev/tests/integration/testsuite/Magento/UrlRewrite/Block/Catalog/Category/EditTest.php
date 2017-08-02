<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block\Catalog\Category;

/**
 * Test for \Magento\UrlRewrite\Block\Catalog\Category\Edit
 * @magentoAppArea adminhtml
 */
class EditTest extends \PHPUnit\Framework\DOMTestCase
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
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $layoutFactory = $objectManager->get(\Magento\Framework\View\LayoutFactory::class);
        /** @var $layout \Magento\Framework\View\LayoutInterface */
        $layout = $layoutFactory->create();

        /** @var $block \Magento\UrlRewrite\Block\Catalog\Category\Edit */
        $block = $layout->createBlock(
            \Magento\UrlRewrite\Block\Catalog\Category\Edit::class,
            '',
            ['data' => $blockAttributes]
        );

        $this->_checkSelector($block, $expected);
        $this->_checkLinks($block, $expected);
        $this->_checkButtons($block, $expected);
        $this->_checkForm($block, $expected);
        $this->_checkCategoriesTree($block, $expected);
    }

    /**
     * Check selector
     *
     * @param \Magento\UrlRewrite\Block\Catalog\Category\Edit $block
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
                \Magento\UrlRewrite\Block\Selector::class,
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
     * @param \Magento\UrlRewrite\Block\Catalog\Category\Edit $block
     * @param array $expected
     */
    private function _checkLinks($block, $expected)
    {
        $layout = $block->getLayout();
        $blockName = $block->getNameInLayout();

        /** @var $categoryBlock \Magento\UrlRewrite\Block\Link|bool */
        $categoryBlock = $layout->getChildBlock($blockName, 'category_link');

        if ($expected['category_link']) {
            $this->assertInstanceOf(
                \Magento\UrlRewrite\Block\Link::class,
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

            $this->assertRegExp(
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
     * @param \Magento\UrlRewrite\Block\Catalog\Category\Edit $block
     * @param array $expected
     */
    private function _checkButtons($block, $expected)
    {
        $buttonsHtml = $block->getButtonsHtml();

        if (isset($expected['back_button'])) {
            if ($expected['back_button']) {
                if ($block->getCategory()->getId()) {
                    $this->assertXpathCount(
                        '//button[contains(@class, "back") and contains(@onclick, "/category")]',
                        1,
                        $buttonsHtml,
                        'Back button is not present in category URL rewrite edit block'
                    );
                } else {
                    $this->assertEquals(
                        1,
                        \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                            '//button[contains(@class,"back")]',
                            $buttonsHtml
                        ),
                        'Back button is not present in category URL rewrite edit block'
                    );
                }
            } else {
                $this->assertEquals(
                    0,
                    \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
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
                \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                    '//button[contains(@class,"save")]',
                    $buttonsHtml
                ),
                'Save button is not present in category URL rewrite edit block'
            );
        } else {
            $this->assertEquals(
                0,
                \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                    '//button[contains(@class,"save")]',
                    $buttonsHtml
                ),
                'Save button should not present in category URL rewrite edit block'
            );
        }

        if ($expected['reset_button']) {
            $this->assertEquals(
                1,
                \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                    '//button[@title="Reset"]',
                    $buttonsHtml
                ),
                'Reset button is not present in category URL rewrite edit block'
            );
        } else {
            $this->assertEquals(
                0,
                \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                    '//button[@title="Reset"]',
                    $buttonsHtml
                ),
                'Reset button should not present in category URL rewrite edit block'
            );
        }

        if ($expected['delete_button']) {
            $this->assertEquals(
                1,
                \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                    '//button[contains(@class,"delete")]',
                    $buttonsHtml
                ),
                'Delete button is not present in category URL rewrite edit block'
            );
        } else {
            $this->assertEquals(
                0,
                \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
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
     * @param \Magento\UrlRewrite\Block\Catalog\Category\Edit $block
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
                \Magento\UrlRewrite\Block\Catalog\Edit\Form::class,
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
     * @param \Magento\UrlRewrite\Block\Catalog\Category\Edit $block
     * @param array $expected
     */
    private function _checkCategoriesTree($block, $expected)
    {
        $layout = $block->getLayout();
        $blockName = $block->getNameInLayout();

        /** @var $categoriesTreeBlock \Magento\UrlRewrite\Block\Catalog\Category\Tree|bool */
        $categoriesTreeBlock = $layout->getChildBlock($blockName, 'categories_tree');

        if ($expected['categories_tree']) {
            $this->assertInstanceOf(
                \Magento\UrlRewrite\Block\Catalog\Category\Tree::class,
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
    public function prepareLayoutDataProvider()
    {
        /** @var $urlRewrite \Magento\UrlRewrite\Model\UrlRewrite */
        $urlRewrite = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\UrlRewrite\Model\UrlRewrite::class
        );
        /** @var $category \Magento\Catalog\Model\Category */
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Category::class,
            ['data' => ['entity_id' => 1, 'name' => 'Test category']]
        );
        /** @var $existingUrlRewrite \Magento\UrlRewrite\Model\UrlRewrite */
        $existingUrlRewrite = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\UrlRewrite\Model\UrlRewrite::class,
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

    /**
     * Assert count of elements found by XPath in XML/HTML string
     *
     * @param string $xpath
     * @param int $count
     * @param string $html
     * @param string|null $message
     */
    public function assertXpathCount($xpath, $count, $html, $message = '')
    {
        $domDocument = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $domDocument->loadHTML($html);
        libxml_use_internal_errors(false);
        $domXpath = new \DOMXPath($domDocument);
        $nodes = $domXpath->query($xpath);
        $this->assertEquals($count, $nodes->length, $message);
    }
}
