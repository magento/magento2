<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block\Cms\Page;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\UrlRewrite\Block\Cms\Page\Edit\Form;
use Magento\UrlRewrite\Block\Edit as UrlRewriteEdit;
use Magento\UrlRewrite\Block\Link;
use Magento\UrlRewrite\Model\UrlRewriteFactory;

/**
 * Block for CMS pages URL rewrites
 */
class Edit extends UrlRewriteEdit
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @param Context $context
     * @param UrlRewriteFactory $rewriteFactory
     * @param BackendHelper $adminhtmlData
     * @param PageFactory $pageFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlRewriteFactory $rewriteFactory,
        BackendHelper $adminhtmlData,
        PageFactory $pageFactory,
        array $data = []
    ) {
        $this->_pageFactory = $pageFactory;
        parent::__construct($context, $rewriteFactory, $adminhtmlData, $data);
    }

    /**
     * Prepare layout for URL rewrite creating for CMS page
     *
     * @return void
     */
    protected function _prepareLayoutFeatures()
    {
        if ($this->_getUrlRewrite()->getId()) {
            $this->_headerText = __('Edit URL Rewrite for CMS page');
        } else {
            $this->_headerText = __('Add URL Rewrite for CMS page');
        }

        if ($this->_getCmsPage()->getId()) {
            $this->_addCmsPageLinkBlock();
            $this->_addEditFormBlock();
            if ($this->_getUrlRewrite()->getId() === null) {
                $this->_updateBackButtonLink($this->_adminhtmlData->getUrl('adminhtml/*/edit') . 'cms_page');
            }
        } else {
            $this->_addUrlRewriteSelectorBlock();
            $this->_addCmsPageGridBlock();
        }
    }

    /**
     * Get or create new instance of CMS page
     *
     * @return Page
     */
    private function _getCmsPage()
    {
        if (!$this->hasData('cms_page')) {
            $this->setCmsPage($this->_pageFactory->create());
        }
        return $this->getCmsPage();
    }

    /**
     * Add child CMS page link block
     *
     * @return void
     */
    private function _addCmsPageLinkBlock()
    {
        $this->addChild(
            'cms_page_link',
            Link::class,
            [
                'item_url' => $this->_adminhtmlData->getUrl('adminhtml/*/*') . 'cms_page',
                'item_name' => $this->getCmsPage()->getTitle(),
                'label' => __('CMS page:')
            ]
        );
    }

    /**
     * Add child CMS page block
     *
     * @return void
     */
    private function _addCmsPageGridBlock()
    {
        $this->addChild('cms_pages_grid', Grid::class);
    }

    /**
     * Creates edit form block
     *
     * @return Form
     */
    protected function _createEditFormBlock()
    {
        return $this->getLayout()->createBlock(
            Form::class,
            '',
            ['data' => ['cms_page' => $this->_getCmsPage(), 'url_rewrite' => $this->_getUrlRewrite()]]
        );
    }
}
