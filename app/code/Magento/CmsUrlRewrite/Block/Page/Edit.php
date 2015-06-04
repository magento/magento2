<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CmsUrlRewrite\Block\Page;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\CmsUrlRewrite\Model\Mode\CmsPage as CmsPageMode;
use Magento\UrlRewrite\Model\UrlRewriteFactory;

/**
 * Block for CMS pages URL rewrites
 */
/**
 * @method \Magento\UrlRewrite\Model\UrlRewrite getUrlRewrite()
 * @method \Magento\Cms\Model\Page getCmsPage()
 * @method Edit setCmsPage(\Magento\Cms\Model\Page $page)
 */
class Edit extends \Magento\UrlRewrite\Block\Edit
{
    /**
     * @var \Magento\CmsUrlRewrite\Model\Mode\CmsPage
     */
    protected $cmsPageMode;

    /**
     * @param Context $context
     * @param UrlRewriteFactory $rewriteFactory
     * @param BackendHelper $adminhtmlData
     * @param CmsPageMode $cmsPageMode
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlRewriteFactory $rewriteFactory,
        BackendHelper $adminhtmlData,
        CmsPageMode $cmsPageMode,
        array $data = []
    ) {
        $this->cmsPageMode = $cmsPageMode;
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
            $this->setCmsPage($this->cmsPageMode->getCmsPage($this->getUrlRewrite()));
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
            'Magento\UrlRewrite\Block\Link',
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
        $this->addChild('cms_pages_grid', 'Magento\CmsUrlRewrite\Block\Page\Grid');
    }

    /**
     * Creates edit form block
     *
     * @return \Magento\UrlRewrite\Block\Cms\Page\Edit\Form
     */
    protected function _createEditFormBlock()
    {
        return $this->getLayout()->createBlock(
            'Magento\CmsUrlRewrite\Block\Page\Edit\Form',
            '',
            [
                'data' => [
                    'cms_page' => $this->_getCmsPage(),
                    'url_rewrite' => $this->_getUrlRewrite()
                ]
            ]
        );
    }
}
