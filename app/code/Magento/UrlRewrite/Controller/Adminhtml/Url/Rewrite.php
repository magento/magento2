<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Controller\Adminhtml\Url;

use Magento\Backend\App\Action;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;

/**
 * URL rewrite adminhtml controller
 * @since 2.0.0
 */
abstract class Rewrite extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_UrlRewrite::urlrewrite';

    /**#@+
     * Entity types
     */
    const ENTITY_TYPE_CUSTOM = 'custom';
    const ENTITY_TYPE_PRODUCT = 'product';
    const ENTITY_TYPE_CATEGORY = 'category';
    const ENTITY_TYPE_CMS_PAGE = 'cms-page';
    /**#@-*/

    /**
     * @var Product
     * @since 2.0.0
     */
    protected $_product;

    /**
     * @var Category
     * @since 2.0.0
     */
    protected $_category;

    /**
     * @var \Magento\Cms\Model\Page
     * @since 2.0.0
     */
    protected $_cmsPage;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewrite
     * @since 2.0.0
     */
    protected $_urlRewrite;

    /**
     * Get Category from request
     *
     * @return Category
     * @since 2.0.0
     */
    protected function _getCategory()
    {
        if (!$this->_category) {
            $this->_category = $this->_objectManager->create(\Magento\Catalog\Model\Category::class);
            $categoryId = (int)$this->getRequest()->getParam('category', 0);
            $urlRewrite = $this->_getUrlRewrite();
            if (!$categoryId && $urlRewrite->getId()) {
                $metaData = $urlRewrite->getMetadata();
                if ($urlRewrite->getEntityType() === self::ENTITY_TYPE_CATEGORY) {
                    $categoryId = $urlRewrite->getEntityId();
                } elseif (!empty($metaData['category_id'])) {
                    $categoryId = $metaData['category_id'];
                }
            }
            if ($categoryId) {
                $this->_category->load($categoryId);
            }
        }
        return $this->_category;
    }

    /**
     * Get Product from request
     *
     * @return Product
     * @since 2.0.0
     */
    protected function _getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_objectManager->create(\Magento\Catalog\Model\Product::class);
            $productId = (int)$this->getRequest()->getParam('product', 0);
            $urlRewrite = $this->_getUrlRewrite();
            if (!$productId && $urlRewrite->getId() && $urlRewrite->getEntityType() === self::ENTITY_TYPE_PRODUCT) {
                $productId = $this->_getUrlRewrite()->getEntityId();
            }
            if ($productId) {
                $this->_product->load($productId);
            }
        }
        return $this->_product;
    }

    /**
     * Get CMS page from request
     *
     * @return \Magento\Cms\Model\Page
     * @since 2.0.0
     */
    protected function _getCmsPage()
    {
        if (!$this->_cmsPage) {
            $this->_cmsPage = $this->_objectManager->create(\Magento\Cms\Model\Page::class);
            $cmsPageId = (int)$this->getRequest()->getParam('cms_page', 0);
            $urlRewrite = $this->_getUrlRewrite();
            if (!$cmsPageId && $urlRewrite->getId() && $urlRewrite->getEntityType() === self::ENTITY_TYPE_CMS_PAGE) {
                $cmsPageId = $this->_getUrlRewrite()->getEntityId();
            }
            if ($cmsPageId) {
                $this->_cmsPage->load($cmsPageId);
            }
        }
        return $this->_cmsPage;
    }

    /**
     * Get URL rewrite from request
     *
     * @return \Magento\UrlRewrite\Model\UrlRewrite
     * @since 2.0.0
     */
    protected function _getUrlRewrite()
    {
        if (!$this->_urlRewrite) {
            $this->_urlRewrite = $this->_objectManager->create(\Magento\UrlRewrite\Model\UrlRewrite::class);
            $urlRewriteId = (int)$this->getRequest()->getParam('id', 0);
            if ($urlRewriteId) {
                $this->_urlRewrite->load($urlRewriteId);
            }
        }
        return $this->_urlRewrite;
    }
}
