<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block;

/**
 * Test for \Magento\UrlRewrite\Block\Edit
 * @magentoAppArea adminhtml
 */
class EditTest extends \PHPUnit\Framework\TestCase
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
            \Magento\Framework\View\LayoutInterface::class
        );

        /** @var $block \Magento\UrlRewrite\Block\Edit */
        $block = $layout->createBlock(\Magento\UrlRewrite\Block\Edit::class, '', ['data' => $blockAttributes]);

        $this->_checkSelector($block, $expected);
        $this->_checkButtons($block, $expected);
        $this->_checkForm($block, $expected);
    }

    /**
     * Check entity selector
     *
     * @param \Magento\UrlRewrite\Block\Edit $block
     * @param array $expected
     */
    private function _checkSelector($block, $expected)
    {
        $layout = $block->getLayout();

        /** @var $selectorBlock \Magento\UrlRewrite\Block\Selector|bool */
        $selectorBlock = $layout->getChildBlock($block->getNameInLayout(), 'selector');

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
     * Check form
     *
     * @param \Magento\UrlRewrite\Block\Edit $block
     * @param array $expected
     */
    private function _checkForm($block, $expected)
    {
        $layout = $block->getLayout();
        $blockName = $block->getNameInLayout();

        /** @var $formBlock \Magento\UrlRewrite\Block\Edit\Form|bool */
        $formBlock = $layout->getChildBlock($blockName, 'form');

        if ($expected['form']) {
            $this->assertInstanceOf(
                \Magento\UrlRewrite\Block\Edit\Form::class,
                $formBlock,
                'Child block with form is invalid'
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
     * Check buttons
     *
     * @param \Magento\UrlRewrite\Block\Edit $block
     * @param array $expected
     */
    private function _checkButtons($block, $expected)
    {
        $buttonsHtml = $block->getButtonsHtml();

        if ($expected['back_button']) {
            $this->assertEquals(
                1,
                \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                    '//button[contains(@class,"back")]',
                    $buttonsHtml
                ),
                'Back button is not present in block'
            );
        } else {
            $this->assertEquals(
                0,
                \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                    '//button[contains(@class,"back")]',
                    $buttonsHtml
                ),
                'Back button should not present in block'
            );
        }

        if ($expected['save_button']) {
            $this->assertEquals(
                1,
                \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                    '//button[contains(@class,"save")]',
                    $buttonsHtml
                ),
                'Save button is not present in block'
            );
        } else {
            $this->assertEquals(
                0,
                \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                    '//button[contains(@class,"save")]',
                    $buttonsHtml
                ),
                'Save button should not present in block'
            );
        }

        if ($expected['reset_button']) {
            $this->assertEquals(
                1,
                \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                    '//button[@title="Reset"]',
                    $buttonsHtml
                ),
                'Reset button is not present in block'
            );
        } else {
            $this->assertEquals(
                0,
                \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                    '//button[@title="Reset"]',
                    $buttonsHtml
                ),
                'Reset button should not present in block'
            );
        }

        if ($expected['delete_button']) {
            $this->assertEquals(
                1,
                \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                    '//button[contains(@class,"delete")]',
                    $buttonsHtml
                ),
                'Delete button is not present in block'
            );
        } else {
            $this->assertEquals(
                0,
                \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                    '//button[contains(@class,"delete")]',
                    $buttonsHtml
                ),
                'Delete button should not present in block'
            );
        }
    }

    /**
     * Data provider
     *
     * @return array
     */
    public static function prepareLayoutDataProvider()
    {
        /** @var $urlRewrite \Magento\UrlRewrite\Model\UrlRewrite */
        $urlRewrite = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\UrlRewrite\Model\UrlRewrite::class
        );
        /** @var $existingUrlRewrite \Magento\UrlRewrite\Model\UrlRewrite */
        $existingUrlRewrite = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\UrlRewrite\Model\UrlRewrite::class,
            ['data' => ['url_rewrite_id' => 1]]
        );

        return [
            // Creating new URL rewrite
            [
                ['url_rewrite' => $urlRewrite],
                [
                    'selector' => true,
                    'back_button' => true,
                    'save_button' => true,
                    'reset_button' => false,
                    'delete_button' => false,
                    'form' => ['url_rewrite' => $urlRewrite]
                ]
            ],
            // Editing URL rewrite
            [
                ['url_rewrite' => $existingUrlRewrite],
                [
                    'selector' => true,
                    'back_button' => true,
                    'save_button' => true,
                    'reset_button' => true,
                    'delete_button' => true,
                    'form' => ['url_rewrite' => $existingUrlRewrite]
                ]
            ]
        ];
    }
}
