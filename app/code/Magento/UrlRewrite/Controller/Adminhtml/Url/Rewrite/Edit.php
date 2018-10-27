<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

class Edit extends \Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite
{
    /**#@+
     * Modes
     */
    const ID_MODE = 'id';
    const PRODUCT_MODE = 'product';
    const CATEGORY_MODE = 'category';
    const CMS_PAGE_MODE = 'cms_page';
    /**#@-*/

    /**
     * Get current mode
     *
     * @return string
     */
    protected function _getMode()
    {
        if ($this->_getProduct()->getId() || $this->getRequest()->has('product')) {
            $mode = self::PRODUCT_MODE;
        } elseif ($this->_getCategory()->getId() || $this->getRequest()->has('category')) {
            $mode = self::CATEGORY_MODE;
        } elseif ($this->_getCmsPage()->getId() || $this->getRequest()->has('cms_page')) {
            $mode = self::CMS_PAGE_MODE;
        } elseif ($this->getRequest()->has('id')) {
            $mode = self::ID_MODE;
        } else {
            $mode = $this->_objectManager->get(\Magento\UrlRewrite\Block\Selector::class)->getDefaultMode();
        }
        return $mode;
    }

    /**
     * Show urlrewrite edit/create page
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_UrlRewrite::urlrewrite');

        $mode = $this->_getMode();
        switch ($mode) {
            case self::PRODUCT_MODE:
                $editBlock = $this->_view->getLayout()->createBlock(
                    \Magento\UrlRewrite\Block\Catalog\Product\Edit::class,
                    '',
                    [
                        'data' => [
                            'category' => $this->_getCategory(),
                            'product' => $this->_getProduct(),
                            'is_category_mode' => $this->getRequest()->has('category'),
                            'url_rewrite' => $this->_getUrlRewrite(),
                        ]
                    ]
                );
                break;
            case self::CATEGORY_MODE:
                $editBlock = $this->_view->getLayout()->createBlock(
                    \Magento\UrlRewrite\Block\Catalog\Category\Edit::class,
                    '',
                    [
                        'data' => ['category' => $this->_getCategory(), 'url_rewrite' => $this->_getUrlRewrite()]
                    ]
                );
                break;
            case self::CMS_PAGE_MODE:
                $editBlock = $this->_view->getLayout()->createBlock(
                    \Magento\UrlRewrite\Block\Cms\Page\Edit::class,
                    '',
                    [
                        'data' => ['cms_page' => $this->_getCmsPage(), 'url_rewrite' => $this->_getUrlRewrite()]
                    ]
                );
                break;
            case self::ID_MODE:
            default:
                $editBlock = $this->_view->getLayout()->createBlock(
                    \Magento\UrlRewrite\Block\Edit::class,
                    '',
                    ['data' => ['url_rewrite' => $this->_getUrlRewrite()]]
                );
                break;
        }
        $this->_view->getPage()->getConfig()->getTitle()->prepend($editBlock->getHeaderText());
        $this->_addContent($editBlock);
        $this->_view->renderLayout();
    }
}
