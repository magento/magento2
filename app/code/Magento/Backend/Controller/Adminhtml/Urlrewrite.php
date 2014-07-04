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
namespace Magento\Backend\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\Model\Exception;

/**
 * URL rewrite adminhtml controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Urlrewrite extends Action
{
    const ID_MODE = 'id';

    const PRODUCT_MODE = 'product';

    const CATEGORY_MODE = 'category';

    const CMS_PAGE_MODE = 'cms_page';

    /**
     * @var Product
     */
    private $_product;

    /**
     * @var Category
     */
    private $_category;

    /**
     * @var \Magento\Cms\Model\Page
     */
    private $_cmsPage;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewrite
     */
    private $_urlRewrite;

    /**
     * Show URL rewrites index page
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('URL Redirects'));

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Catalog::catalog_urlrewrite');
        $this->_view->renderLayout();
    }

    /**
     * Show urlrewrite edit/create page
     *
     * @return void
     */
    public function editAction()
    {
        $this->_title->add(__('URL Redirects'));
        $this->_title->add(__('[New/Edit] URL Redirect'));

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Catalog::catalog_urlrewrite');

        $mode = $this->_getMode();

        switch ($mode) {
            case self::PRODUCT_MODE:
                $editBlock = $this->_view->getLayout()->createBlock(
                    'Magento\Backend\Block\Urlrewrite\Catalog\Product\Edit',
                    '',
                    array(
                        'data' => array(
                            'category' => $this->_getCategory(),
                            'product' => $this->_getProduct(),
                            'is_category_mode' => $this->getRequest()->has('category'),
                            'url_rewrite' => $this->_getUrlRewrite()
                        )
                    )
                );
                break;
            case self::CATEGORY_MODE:
                $editBlock = $this->_view->getLayout()->createBlock(
                    'Magento\Backend\Block\Urlrewrite\Catalog\Category\Edit',
                    '',
                    array(
                        'data' => array('category' => $this->_getCategory(), 'url_rewrite' => $this->_getUrlRewrite())
                    )
                );
                break;
            case self::CMS_PAGE_MODE:
                $editBlock = $this->_view->getLayout()->createBlock(
                    'Magento\Backend\Block\Urlrewrite\Cms\Page\Edit',
                    '',
                    array(
                        'data' => array('cms_page' => $this->_getCmsPage(), 'url_rewrite' => $this->_getUrlRewrite())
                    )
                );
                break;
            case self::ID_MODE:
            default:
                $editBlock = $this->_view->getLayout()->createBlock(
                    'Magento\Backend\Block\Urlrewrite\Edit',
                    '',
                    array('data' => array('url_rewrite' => $this->_getUrlRewrite()))
                );
                break;
        }

        $this->_addContent($editBlock);
        if (in_array($mode, array(self::PRODUCT_MODE, self::CATEGORY_MODE))) {
            $this->_view->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        }
        $this->_view->renderLayout();
    }

    /**
     * Get current mode
     *
     * @return string
     */
    private function _getMode()
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
            $mode = $this->_objectManager->get('Magento\Backend\Block\Urlrewrite\Selector')->getDefaultMode();
        }
        return $mode;
    }

    /**
     * Ajax products grid action
     *
     * @return void
     */
    public function productGridAction()
    {
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock('Magento\Backend\Block\Urlrewrite\Catalog\Product\Grid')->toHtml()
        );
    }

    /**
     * Ajax categories tree loader action
     *
     * @return void
     */
    public function categoriesJsonAction()
    {
        $categoryId = $this->getRequest()->getParam('id', null);
        $this->getResponse()->representJson(
            $this->_objectManager->get(
                'Magento\Backend\Block\Urlrewrite\Catalog\Category\Tree'
            )->getTreeArray(
                $categoryId,
                true,
                1
            )
        );
    }

    /**
     * Ajax CMS pages grid action
     *
     * @return void
     */
    public function cmsPageGridAction()
    {
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock('Magento\Backend\Block\Urlrewrite\Cms\Page\Grid')->toHtml()
        );
    }

    /**
     * Urlrewrite save action
     *
     * @return void
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            /** @var $session \Magento\Backend\Model\Session */
            $session = $this->_objectManager->get('Magento\Backend\Model\Session');
            try {
                // set basic urlrewrite data
                /** @var $model \Magento\UrlRewrite\Model\UrlRewrite */
                $model = $this->_getUrlRewrite();

                // Validate request path
                $requestPath = $this->getRequest()->getParam('request_path');
                $this->_objectManager->get('Magento\UrlRewrite\Helper\UrlRewrite')->validateRequestPath($requestPath);

                // Proceed and save request
                $model->setIdPath(
                    $this->getRequest()->getParam('id_path')
                )->setTargetPath(
                    $this->getRequest()->getParam('target_path')
                )->setOptions(
                    $this->getRequest()->getParam('options')
                )->setDescription(
                    $this->getRequest()->getParam('description')
                )->setRequestPath(
                    $requestPath
                );

                if (!$model->getId()) {
                    $model->setIsSystem(0);
                }
                if (!$model->getIsSystem()) {
                    $model->setStoreId($this->getRequest()->getParam('store_id', 0));
                }

                $this->_onUrlRewriteSaveBefore($model);

                // save and redirect
                $model->save();

                $this->_onUrlRewriteSaveAfter($model);

                $this->messageManager->addSuccess(__('The URL Rewrite has been saved.'));
                $this->_redirect('adminhtml/*/');
                return;
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $session->setUrlrewriteData($data);
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('An error occurred while saving URL Rewrite.'));
                $session->setUrlrewriteData($data);
            }
        }
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
    }

    /**
     * Call before save urlrewrite handlers
     *
     * @param \Magento\UrlRewrite\Model\UrlRewrite $model
     * @return void
     */
    protected function _onUrlRewriteSaveBefore($model)
    {
        $this->_handleCatalogUrlRewrite($model);
        $this->_handleCmsPageUrlRewrite($model);
    }

    /**
     * Call after save urlrewrite handlers
     *
     * @param \Magento\UrlRewrite\Model\UrlRewrite $model
     * @return void
     */
    protected function _onUrlRewriteSaveAfter($model)
    {
        $this->_handleCmsPageUrlRewriteSave($model);
    }

    /**
     * Override urlrewrite data, basing on current category and product
     *
     * @param \Magento\UrlRewrite\Model\UrlRewrite $model
     * @return void
     * @throws Exception
     */
    protected function _handleCatalogUrlRewrite($model)
    {
        $product = $this->_getInitializedProduct($model);
        $category = $this->_getInitializedCategory($model);

        if ($product || $category) {
            /** @var $catalogUrlModel \Magento\Catalog\Model\Url */
            $catalogUrlModel = $this->_objectManager->get('Magento\Catalog\Model\Url');
            $idPath = $catalogUrlModel->generatePath('id', $product, $category);
            $model->setIdPath($idPath);

            // if redirect specified try to find friendly URL
            $generateTarget = true;
            if ($this->_objectManager->get('Magento\UrlRewrite\Helper\UrlRewrite')->hasRedirectOptions($model)) {
                /** @var $rewriteResource \Magento\Catalog\Model\Resource\Url */
                $rewriteResource = $this->_objectManager->create('Magento\Catalog\Model\Resource\Url');
                /** @var $rewrite \Magento\UrlRewrite\Model\UrlRewrite */
                $rewrite = $rewriteResource->getRewriteByIdPath($idPath, $model->getStoreId());
                if (!$rewrite) {
                    if ($product) {
                        throw new Exception(
                            __('Chosen product does not associated with the chosen store or category.')
                        );
                    } else {
                        throw new Exception(__('Chosen category does not associated with the chosen store.'));
                    }
                } elseif ($rewrite->getId() && $rewrite->getId() != $model->getId()) {
                    $model->setTargetPath($rewrite->getRequestPath());
                    $generateTarget = false;
                }
            }
            if ($generateTarget) {
                $model->setTargetPath($catalogUrlModel->generatePath('target', $product, $category));
            }
        }
    }

    /**
     * Get product instance applicable for generatePath
     *
     * @param \Magento\UrlRewrite\Model\UrlRewrite $model
     * @return Product|null
     */
    private function _getInitializedProduct($model)
    {
        /** @var $product Product */
        $product = $this->_getProduct();
        if ($product->getId()) {
            $model->setProductId($product->getId());
        } else {
            $product = null;
        }

        return $product;
    }

    /**
     * Get category instance applicable for generatePath
     *
     * @param \Magento\UrlRewrite\Model\UrlRewrite $model
     * @return Category|null
     */
    private function _getInitializedCategory($model)
    {
        /** @var $category Category */
        $category = $this->_getCategory();
        if ($category->getId()) {
            $model->setCategoryId($category->getId());
        } else {
            $category = null;
        }
        return $category;
    }

    /**
     * Override URL rewrite data, basing on current CMS page
     *
     * @param \Magento\UrlRewrite\Model\UrlRewrite $model
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    private function _handleCmsPageUrlRewrite($model)
    {
        /** @var $cmsPage \Magento\Cms\Model\Page */
        $cmsPage = $this->_getCmsPage();
        if (!$cmsPage->getId()) {
            return;
        }

        /** @var $cmsPageUrlRewrite \Magento\Cms\Model\Page\Urlrewrite */
        $cmsPageUrlRewrite = $this->_objectManager->create('Magento\Cms\Model\Page\Urlrewrite');
        $idPath = $cmsPageUrlRewrite->generateIdPath($cmsPage);
        $model->setIdPath($idPath);

        // if redirect specified try to find friendly URL
        $generateTarget = true;
        if ($this->_objectManager->get('Magento\UrlRewrite\Helper\UrlRewrite')->hasRedirectOptions($model)) {
            /** @var $rewriteResource \Magento\Catalog\Model\Resource\Url */
            $rewriteResource = $this->_objectManager->create('Magento\Catalog\Model\Resource\Url');
            /** @var $rewrite \Magento\UrlRewrite\Model\UrlRewrite */
            $rewrite = $rewriteResource->getRewriteByIdPath($idPath, $model->getStoreId());
            if (!$rewrite) {
                throw new Exception(__('Chosen cms page does not associated with the chosen store.'));
            } elseif ($rewrite->getId() && $rewrite->getId() != $model->getId()) {
                $model->setTargetPath($rewrite->getRequestPath());
                $generateTarget = false;
            }
        }

        if ($generateTarget) {
            $model->setTargetPath($cmsPageUrlRewrite->generateTargetPath($cmsPage));
        }
    }

    /**
     * Save CMS page URL rewrite additional information
     *
     * @param \Magento\UrlRewrite\Model\UrlRewrite $model
     * @return void
     */
    private function _handleCmsPageUrlRewriteSave($model)
    {
        /** @var $cmsPage \Magento\Cms\Model\Page */
        $cmsPage = $this->_getCmsPage();
        if (!$cmsPage->getId()) {
            return;
        }

        /** @var $cmsRewrite \Magento\Cms\Model\Page\Urlrewrite */
        $cmsRewrite = $this->_objectManager->create('Magento\Cms\Model\Page\Urlrewrite');
        $cmsRewrite->load($model->getId(), 'url_rewrite_id');
        if (!$cmsRewrite->getId()) {
            $cmsRewrite->setUrlRewriteId($model->getId());
            $cmsRewrite->setCmsPageId($cmsPage->getId());
            $cmsRewrite->save();
        }
    }

    /**
     * URL rewrite delete action
     *
     * @return void
     */
    public function deleteAction()
    {
        if ($this->_getUrlRewrite()->getId()) {
            try {
                $this->_getUrlRewrite()->delete();
                $this->messageManager->addSuccess(__('The URL Rewrite has been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('An error occurred while deleting URL Rewrite.'));
                $this->_redirect('adminhtml/*/edit/', array('id' => $this->_getUrlRewrite()->getId()));
                return;
            }
        }
        $this->_redirect('adminhtml/*/');
    }

    /**
     * Check whether this contoller is allowed in admin permissions
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::urlrewrite');
    }

    /**
     * Get Category from request
     *
     * @return Category
     */
    private function _getCategory()
    {
        if (!$this->_category) {
            $this->_category = $this->_objectManager->create('Magento\Catalog\Model\Category');
            $categoryId = (int)$this->getRequest()->getParam('category', 0);

            if (!$categoryId && $this->_getUrlRewrite()->getId()) {
                $categoryId = $this->_getUrlRewrite()->getCategoryId();
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
     */
    private function _getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_objectManager->create('Magento\Catalog\Model\Product');
            $productId = (int)$this->getRequest()->getParam('product', 0);

            if (!$productId && $this->_getUrlRewrite()->getId()) {
                $productId = $this->_getUrlRewrite()->getProductId();
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
     */
    private function _getCmsPage()
    {
        if (!$this->_cmsPage) {
            $this->_cmsPage = $this->_objectManager->create('Magento\Cms\Model\Page');
            $cmsPageId = (int)$this->getRequest()->getParam('cms_page', 0);

            if (!$cmsPageId && $this->_getUrlRewrite()->getId()) {
                $urlRewriteId = $this->_getUrlRewrite()->getId();
                /** @var $cmsUrlRewrite \Magento\Cms\Model\Page\Urlrewrite */
                $cmsUrlRewrite = $this->_objectManager->create('Magento\Cms\Model\Page\Urlrewrite');
                $cmsUrlRewrite->load($urlRewriteId, 'url_rewrite_id');
                $cmsPageId = $cmsUrlRewrite->getCmsPageId();
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
     */
    private function _getUrlRewrite()
    {
        if (!$this->_urlRewrite) {
            $this->_urlRewrite = $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite');

            $urlRewriteId = (int)$this->getRequest()->getParam('id', 0);
            if ($urlRewriteId) {
                $this->_urlRewrite->load((int)$this->getRequest()->getParam('id', 0));
            }
        }
        return $this->_urlRewrite;
    }
}
