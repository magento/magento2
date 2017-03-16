<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block\Cms\Page;

/**
 * Block for CMS pages URL rewrites
 */
class Edit extends \Magento\UrlRewrite\Block\Edit
{
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $rewriteFactory
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $rewriteFactory,
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Magento\Cms\Model\PageFactory $pageFactory,
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
     * @return \Magento\Cms\Model\Page
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
            \Magento\UrlRewrite\Block\Link::class,
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
        $this->addChild('cms_pages_grid', \Magento\UrlRewrite\Block\Cms\Page\Grid::class);
    }

    /**
     * Creates edit form block
     *
     * @return \Magento\UrlRewrite\Block\Cms\Page\Edit\Form
     */
    protected function _createEditFormBlock()
    {
        return $this->getLayout()->createBlock(
            \Magento\UrlRewrite\Block\Cms\Page\Edit\Form::class,
            '',
            ['data' => ['cms_page' => $this->_getCmsPage(), 'url_rewrite' => $this->_getUrlRewrite()]]
        );
    }
}
