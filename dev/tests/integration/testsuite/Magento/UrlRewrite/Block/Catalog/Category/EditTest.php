<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\UrlRewrite\Block\Catalog\Category;

/**
 * Test for \Magento\UrlRewrite\Block\Catalog\Category\Edit
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

        /** @var $block \Magento\UrlRewrite\Block\Catalog\Category\Edit */
        $block = $layout->createBlock(
            'Magento\UrlRewrite\Block\Catalog\Category\Edit',
            '',
            array('data' => $blockAttributes)
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
                'Magento\UrlRewrite\Block\Link',
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
                    $this->assertSelectCount(
                        'button.back[onclick~="\/category"]',
                        1,
                        $buttonsHtml,
                        'Back button is not present in category URL rewrite edit block'
                    );
                } else {
                    $this->assertSelectCount(
                        'button.back',
                        1,
                        $buttonsHtml,
                        'Back button is not present in category URL rewrite edit block'
                    );
                }
            } else {
                $this->assertSelectCount(
                    'button.back',
                    0,
                    $buttonsHtml,
                    'Back button should not present in category URL rewrite edit block'
                );
            }
        }

        if ($expected['save_button']) {
            $this->assertSelectCount(
                'button.save',
                1,
                $buttonsHtml,
                'Save button is not present in category URL rewrite edit block'
            );
        } else {
            $this->assertSelectCount(
                'button.save',
                0,
                $buttonsHtml,
                'Save button should not present in category URL rewrite edit block'
            );
        }

        if ($expected['reset_button']) {
            $this->assertSelectCount(
                'button[title="Reset"]',
                1,
                $buttonsHtml,
                'Reset button is not present in category URL rewrite edit block'
            );
        } else {
            $this->assertSelectCount(
                'button[title="Reset"]',
                0,
                $buttonsHtml,
                'Reset button should not present in category URL rewrite edit block'
            );
        }

        if ($expected['delete_button']) {
            $this->assertSelectCount(
                'button.delete',
                1,
                $buttonsHtml,
                'Delete button is not present in category URL rewrite edit block'
            );
        } else {
            $this->assertSelectCount(
                'button.delete',
                0,
                $buttonsHtml,
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
                'Magento\UrlRewrite\Block\Catalog\Edit\Form',
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
                'Magento\UrlRewrite\Block\Catalog\Category\Tree',
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
            'Magento\UrlRewrite\Model\UrlRewrite'
        );
        /** @var $category \Magento\Catalog\Model\Category */
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Category',
            array('data' => array('entity_id' => 1, 'name' => 'Test category'))
        );
        /** @var $existingUrlRewrite \Magento\UrlRewrite\Model\UrlRewrite */
        $existingUrlRewrite = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\UrlRewrite\Model\UrlRewrite',
            array('data' => array('url_rewrite_id' => 1))
        );

        return array(
            // Creating URL rewrite when category selected
            array(
                array('category' => $category, 'url_rewrite' => $urlRewrite),
                array(
                    'selector' => false,
                    'category_link' => array('name' => $category->getName()),
                    'back_button' => true,
                    'save_button' => true,
                    'reset_button' => false,
                    'delete_button' => false,
                    'form' => array('category' => $category, 'url_rewrite' => $urlRewrite),
                    'categories_tree' => false
                )
            ),
            // Creating URL rewrite when category not selected
            array(
                array('url_rewrite' => $urlRewrite),
                array(
                    'selector' => true,
                    'category_link' => false,
                    'back_button' => true,
                    'save_button' => false,
                    'reset_button' => false,
                    'delete_button' => false,
                    'form' => false,
                    'categories_tree' => true
                )
            ),
            // Editing URL rewrite with category
            array(
                array('url_rewrite' => $existingUrlRewrite, 'category' => $category),
                array(
                    'selector' => false,
                    'category_link' => array('name' => $category->getName()),
                    'save_button' => true,
                    'reset_button' => true,
                    'delete_button' => true,
                    'form' => array('category' => $category, 'url_rewrite' => $existingUrlRewrite),
                    'categories_tree' => false
                )
            )
        );
    }
}
