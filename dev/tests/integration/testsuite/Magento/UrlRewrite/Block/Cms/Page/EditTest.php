<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block\Cms\Page;

/**
 * Test for \Magento\UrlRewrite\Block\Cms\Page\Edit
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

        /** @var $block \Magento\UrlRewrite\Block\Cms\Page\Edit */
        $block = $layout->createBlock(
            'Magento\UrlRewrite\Block\Cms\Page\Edit',
            '',
            ['data' => $blockAttributes]
        );

        $this->_checkSelector($block, $expected);
        $this->_checkLinks($block, $expected);
        $this->_checkButtons($block, $expected);
        $this->_checkForm($block, $expected);
        $this->_checkGrid($block, $expected);
    }

    /**
     * Check selector
     *
     * @param \Magento\UrlRewrite\Block\Cms\Page\Edit $block
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
     * @param \Magento\UrlRewrite\Block\Cms\Page\Edit $block
     * @param array $expected
     */
    private function _checkLinks($block, $expected)
    {
        $layout = $block->getLayout();
        $blockName = $block->getNameInLayout();

        /** @var $cmsPageLinkBlock \Magento\UrlRewrite\Block\Link|bool */
        $cmsPageLinkBlock = $layout->getChildBlock($blockName, 'cms_page_link');

        if ($expected['cms_page_link']) {
            $this->assertInstanceOf(
                'Magento\UrlRewrite\Block\Link',
                $cmsPageLinkBlock,
                'Child block with CMS page link is invalid'
            );

            $this->assertEquals(
                'CMS page:',
                $cmsPageLinkBlock->getLabel(),
                'Child block with CMS page has invalid item label'
            );

            $this->assertEquals(
                $expected['cms_page_link']['name'],
                $cmsPageLinkBlock->getItemName(),
                'Child block with CMS page has invalid item name'
            );

            $this->assertRegExp(
                '/http:\/\/localhost\/index.php\/.*\/cms_page/',
                $cmsPageLinkBlock->getItemUrl(),
                'Child block with CMS page contains invalid URL'
            );
        } else {
            $this->assertFalse($cmsPageLinkBlock, 'Child block with CMS page link should not present in block');
        }
    }

    /**
     * Check buttons
     *
     * @param \Magento\UrlRewrite\Block\Cms\Page\Edit $block
     * @param array $expected
     */
    private function _checkButtons($block, $expected)
    {
        $buttonsHtml = $block->getButtonsHtml();

        if (isset($expected['back_button'])) {
            if ($expected['back_button']) {
                if ($block->getCmsPage()->getId()) {
                    $this->assertSelectCount(
                        'button.back[onclick~="\/cms_page"]',
                        1,
                        $buttonsHtml,
                        'Back button is not present in CMS page URL rewrite edit block'
                    );
                } else {
                    $this->assertSelectCount(
                        'button.back',
                        1,
                        $buttonsHtml,
                        'Back button is not present in CMS page URL rewrite edit block'
                    );
                }
            } else {
                $this->assertSelectCount(
                    'button.back',
                    0,
                    $buttonsHtml,
                    'Back button should not present in CMS page URL rewrite edit block'
                );
            }
        }

        if ($expected['save_button']) {
            $this->assertSelectCount(
                'button.save',
                1,
                $buttonsHtml,
                'Save button is not present in CMS page URL rewrite edit block'
            );
        } else {
            $this->assertSelectCount(
                'button.save',
                0,
                $buttonsHtml,
                'Save button should not present in CMS page URL rewrite edit block'
            );
        }

        if ($expected['reset_button']) {
            $this->assertSelectCount(
                'button[title="Reset"]',
                1,
                $buttonsHtml,
                'Reset button is not present in CMS page URL rewrite edit block'
            );
        } else {
            $this->assertSelectCount(
                'button[title="Reset"]',
                0,
                $buttonsHtml,
                'Reset button should not present in CMS page URL rewrite edit block'
            );
        }

        if ($expected['delete_button']) {
            $this->assertSelectCount(
                'button.delete',
                1,
                $buttonsHtml,
                'Delete button is not present in CMS page URL rewrite edit block'
            );
        } else {
            $this->assertSelectCount(
                'button.delete',
                0,
                $buttonsHtml,
                'Delete button should not present in CMS page URL rewrite edit block'
            );
        }
    }

    /**
     * Check form
     *
     * @param \Magento\UrlRewrite\Block\Cms\Page\Edit $block
     * @param array $expected
     */
    private function _checkForm($block, $expected)
    {
        $layout = $block->getLayout();
        $blockName = $block->getNameInLayout();

        /** @var $formBlock \Magento\UrlRewrite\Block\Cms\Page\Edit\Form|bool */
        $formBlock = $layout->getChildBlock($blockName, 'form');

        if ($expected['form']) {
            $this->assertInstanceOf(
                'Magento\UrlRewrite\Block\Cms\Page\Edit\Form',
                $formBlock,
                'Child block with form is invalid'
            );

            $this->assertSame(
                $expected['form']['cms_page'],
                $formBlock->getCmsPage(),
                'Form block should have same CMS page attribute'
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
     * Check grid
     *
     * @param \Magento\UrlRewrite\Block\Cms\Page\Edit $block
     * @param array $expected
     */
    private function _checkGrid($block, $expected)
    {
        $layout = $block->getLayout();
        $blockName = $block->getNameInLayout();

        /** @var $gridBlock \Magento\UrlRewrite\Block\Cms\Page\Grid|bool */
        $gridBlock = $layout->getChildBlock($blockName, 'cms_pages_grid');

        if ($expected['cms_pages_grid']) {
            $this->assertInstanceOf(
                'Magento\UrlRewrite\Block\Cms\Page\Grid',
                $gridBlock,
                'Child block with CMS pages grid is invalid'
            );
        } else {
            $this->assertFalse($gridBlock, 'Child block with CMS pages grid should not present in block');
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
        /** @var $cmsPage \Magento\Cms\Model\Page */
        $cmsPage = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Cms\Model\Page',
            ['data' => ['page_id' => 1, 'title' => 'Test CMS Page']]
        );
        /** @var $existingUrlRewrite \Magento\UrlRewrite\Model\UrlRewrite */
        $existingUrlRewrite = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\UrlRewrite\Model\UrlRewrite',
            ['data' => ['url_rewrite_id' => 1]]
        );

        return [
            // Creating URL rewrite when CMS page selected
            [
                ['cms_page' => $cmsPage, 'url_rewrite' => $urlRewrite],
                [
                    'selector' => false,
                    'cms_page_link' => ['name' => $cmsPage->getTitle()],
                    'back_button' => true,
                    'save_button' => true,
                    'reset_button' => false,
                    'delete_button' => false,
                    'form' => ['cms_page' => $cmsPage, 'url_rewrite' => $urlRewrite],
                    'cms_pages_grid' => false
                ]
            ],
            // Creating URL rewrite when CMS page not selected
            [
                ['url_rewrite' => $urlRewrite],
                [
                    'selector' => true,
                    'cms_page_link' => false,
                    'back_button' => true,
                    'save_button' => false,
                    'reset_button' => false,
                    'delete_button' => false,
                    'form' => false,
                    'cms_pages_grid' => true
                ]
            ],
            // Editing existing URL rewrite with CMS page
            [
                ['url_rewrite' => $existingUrlRewrite, 'cms_page' => $cmsPage],
                [
                    'selector' => false,
                    'cms_page_link' => ['name' => $cmsPage->getTitle()],
                    'save_button' => true,
                    'reset_button' => true,
                    'delete_button' => true,
                    'form' => ['cms_page' => $cmsPage, 'url_rewrite' => $existingUrlRewrite],
                    'cms_pages_grid' => false
                ]
            ]
        ];
    }
}
