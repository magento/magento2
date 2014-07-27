<?php
/**
 *
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
namespace Magento\Backend\Controller\Adminhtml\Urlrewrite;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\Model\Exception;

class Save extends \Magento\Backend\Controller\Adminhtml\Urlrewrite
{
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
    protected function _getInitializedProduct($model)
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
     * Override URL rewrite data, basing on current CMS page
     *
     * @param \Magento\UrlRewrite\Model\UrlRewrite $model
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _handleCmsPageUrlRewrite($model)
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
        if ($model->getId()
            && $this->_objectManager->get('Magento\UrlRewrite\Helper\UrlRewrite')->hasRedirectOptions($model)
        ) {
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
    protected function _handleCmsPageUrlRewriteSave($model)
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
     * Get category instance applicable for generatePath
     *
     * @param \Magento\UrlRewrite\Model\UrlRewrite $model
     * @return Category|null
     */
    protected function _getInitializedCategory($model)
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
     * Urlrewrite save action
     *
     * @return void
     */
    public function execute()
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
}
