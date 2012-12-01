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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * URL rewrite adminhtml controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_UrlrewriteController extends Mage_Adminhtml_Controller_Action
{
    const ID_MODE = 'id';
    const PRODUCT_MODE = 'product';
    const CATEGORY_MODE = 'category';
    const CMS_PAGE_MODE = 'cms_page';

    /**
     * @var Mage_Catalog_Model_Product
     */
    private $_product;

    /**
     * @var Mage_Catalog_Model_Category
     */
    private $_category;

    /**
     * @var Mage_Cms_Model_Page
     */
    private $_cmsPage;

    /**
     * @var Mage_Core_Model_Url_Rewrite
     */
    private $_urlRewrite;

    /**
     * Show URL rewrites index page
     */
    public function indexAction()
    {
        $this->_title($this->__('Rewrite Rules'));

        $this->loadLayout();
        $this->_setActiveMenu('Mage_Catalog::catalog_urlrewrite');
        $this->_addContent(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Urlrewrite')
        );
        $this->renderLayout();
    }

    /**
     * Show urlrewrite edit/create page
     */
    public function editAction()
    {
        $this->_title($this->__('Rewrite Rules'))
            ->_title($this->__('URL Rewrite'));

        $this->loadLayout();
        $this->_setActiveMenu('Mage_Catalog::catalog_urlrewrite');

        $mode = $this->_getMode();

        switch ($mode) {
            case self::PRODUCT_MODE:
                $editBlock = $this->getLayout()
                    ->createBlock('Mage_Adminhtml_Block_Urlrewrite_Catalog_Product_Edit', '', array(
                        'category'         => $this->_getCategory(),
                        'product'          => $this->_getProduct(),
                        'is_category_mode' => $this->getRequest()->has('category'),
                        'url_rewrite'      => $this->_getUrlRewrite()
                    ));
                break;
            case self::CATEGORY_MODE:
                $editBlock = $this->getLayout()
                    ->createBlock('Mage_Adminhtml_Block_Urlrewrite_Catalog_Category_Edit', '', array(
                        'category' => $this->_getCategory(),
                        'url_rewrite' => $this->_getUrlRewrite()
                    ));
                break;
            case self::CMS_PAGE_MODE:
                $editBlock = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Urlrewrite_Cms_Page_Edit', '', array(
                    'cms_page'    => $this->_getCmsPage(),
                    'url_rewrite' => $this->_getUrlRewrite()
                ));
                break;
            case self::ID_MODE:
            default:
                $editBlock = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Urlrewrite_Edit', '', array(
                    'url_rewrite' => $this->_getUrlRewrite()
                ));
                break;
        }

        $this->_addContent($editBlock);
        if (in_array($mode, array(self::PRODUCT_MODE, self::CATEGORY_MODE))) {
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        }
        $this->renderLayout();
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
            $mode = Mage::getBlockSingleton('Mage_Adminhtml_Block_Urlrewrite_Selector')->getDefaultMode();
        }
        return $mode;
    }

    /**
     * Ajax products grid action
     */
    public function productGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Urlrewrite_Catalog_Product_Grid')->toHtml()
        );
    }

    /**
     * Ajax categories tree loader action
     */
    public function categoriesJsonAction()
    {
        $categoryId = $this->getRequest()->getParam('id', null);
        $this->getResponse()->setBody(Mage::getBlockSingleton('Mage_Adminhtml_Block_Urlrewrite_Catalog_Category_Tree')
            ->getTreeArray($categoryId, true, 1)
        );
    }

    /**
     * Ajax CMS pages grid action
     */
    public function cmsPageGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Urlrewrite_Cms_Page_Grid')->toHtml()
        );
    }

    /**
     * Urlrewrite save action
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            /** @var $session Mage_Adminhtml_Model_Session */
            $session = Mage::getSingleton('Mage_Adminhtml_Model_Session');
            try {
                // set basic urlrewrite data
                /** @var $model Mage_Core_Model_Url_Rewrite */
                $model = $this->_getUrlRewrite();

                // Validate request path
                $requestPath = $this->getRequest()->getParam('request_path');
                Mage::helper('Mage_Core_Helper_Url_Rewrite')->validateRequestPath($requestPath);

                // Proceed and save request
                $model->setIdPath($this->getRequest()->getParam('id_path'))
                    ->setTargetPath($this->getRequest()->getParam('target_path'))
                    ->setOptions($this->getRequest()->getParam('options'))
                    ->setDescription($this->getRequest()->getParam('description'))
                    ->setRequestPath($requestPath);

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

                $session->addSuccess(Mage::helper('Mage_Adminhtml_Helper_Data')->__('The URL Rewrite has been saved.'));
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage())
                    ->setUrlrewriteData($data);
            } catch (Exception $e) {
                $session->addException($e,
                    Mage::helper('Mage_Adminhtml_Helper_Data')->__('An error occurred while saving URL Rewrite.'))
                    ->setUrlrewriteData($data);
            }
        }
        $this->_redirectReferer();
    }

    /**
     * Call before save urlrewrite handlers
     *
     * @param Mage_Core_Model_Url_Rewrite $model
     */
    protected function _onUrlRewriteSaveBefore($model)
    {
        $this->_handleCatalogUrlRewrite($model);
        $this->_handleCmsPageUrlRewrite($model);
    }

    /**
     * Call after save urlrewrite handlers
     *
     * @param Mage_Core_Model_Url_Rewrite $model
     */
    protected function _onUrlRewriteSaveAfter($model)
    {
        $this->_handleCmsPageUrlRewriteSave($model);
    }

    /**
     * Override urlrewrite data, basing on current category and product
     *
     * @param Mage_Core_Model_Url_Rewrite $model
     */
    protected function _handleCatalogUrlRewrite($model)
    {
        $product = $this->_getInitializedProduct($model);
        $category = $this->_getInitializedCategory($model);

        if ($product || $category) {
            /** @var $catalogUrlModel Mage_Catalog_Model_Url */
            $catalogUrlModel = Mage::getSingleton('Mage_Catalog_Model_Url');
            $idPath = $catalogUrlModel->generatePath('id', $product, $category);
            $model->setIdPath($idPath);

            // if redirect specified try to find friendly URL
            $generateTarget = true;
            if (Mage::helper('Mage_Core_Helper_Url_Rewrite')->hasRedirectOptions($model)) {
                /** @var $rewriteResource Mage_Catalog_Model_Resource_Url */
                $rewriteResource = Mage::getResourceModel('Mage_Catalog_Model_Resource_Url');
                /** @var $rewrite Mage_Core_Model_Url_Rewrite */
                $rewrite = $rewriteResource->getRewriteByIdPath($idPath, $model->getStoreId());
                if (!$rewrite) {
                    if ($product) {
                        Mage::throwException(Mage::helper('Mage_Adminhtml_Helper_Data')
                            ->__('Chosen product does not associated with the chosen store or category.'));
                    } else {
                        Mage::throwException(Mage::helper('Mage_Adminhtml_Helper_Data')
                            ->__('Chosen category does not associated with the chosen store.'));
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
     * @param Mage_Core_Model_Url_Rewrite $model
     * @return Mage_Catalog_Model_Product|null
     */
    private function _getInitializedProduct($model)
    {
        /** @var $product Mage_Catalog_Model_Product */
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
     * @param Mage_Core_Model_Url_Rewrite $model
     * @return Mage_Catalog_Model_Category|null
     */
    private function _getInitializedCategory($model)
    {
        /** @var $category Mage_Catalog_Model_Category */
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
     * @param Mage_Core_Model_Url_Rewrite $model
     */
    private function _handleCmsPageUrlRewrite($model)
    {
        /** @var $cmsPage Mage_Cms_Model_Page */
        $cmsPage = $this->_getCmsPage();
        if (!$cmsPage->getId()) {
            return;
        }

        /** @var $cmsPageUrlRewrite Mage_Cms_Model_Page_Urlrewrite */
        $cmsPageUrlRewrite = Mage::getModel('Mage_Cms_Model_Page_Urlrewrite');
        $idPath = $cmsPageUrlRewrite->generateIdPath($cmsPage);
        $model->setIdPath($idPath);

        // if redirect specified try to find friendly URL
        $generateTarget = true;
        if (Mage::helper('Mage_Core_Helper_Url_Rewrite')->hasRedirectOptions($model)) {
            /** @var $rewriteResource Mage_Catalog_Model_Resource_Url */
            $rewriteResource = Mage::getResourceModel('Mage_Catalog_Model_Resource_Url');
            /** @var $rewrite Mage_Core_Model_Url_Rewrite */
            $rewrite = $rewriteResource->getRewriteByIdPath($idPath, $model->getStoreId());
            if (!$rewrite) {
                Mage::throwException(Mage::helper('Mage_Adminhtml_Helper_Data')
                    ->__('Chosen cms page does not associated with the chosen store.'));
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
     * @param Mage_Core_Model_Url_Rewrite $model
     */
    private function _handleCmsPageUrlRewriteSave($model)
    {
        /** @var $cmsPage Mage_Cms_Model_Page */
        $cmsPage = $this->_getCmsPage();
        if (!$cmsPage->getId()) {
            return;
        }

        /** @var $cmsRewrite Mage_Cms_Model_Page_Urlrewrite */
        $cmsRewrite = Mage::getModel('Mage_Cms_Model_Page_Urlrewrite');
        $cmsRewrite->load($model->getId(), 'url_rewrite_id');
        if (!$cmsRewrite->getId()) {
            $cmsRewrite->setUrlRewriteId($model->getId());
            $cmsRewrite->setCmsPageId($cmsPage->getId());
            $cmsRewrite->save();
        }
    }

    /**
     * URL rewrite delete action
     */
    public function deleteAction()
    {
        if ($this->_getUrlRewrite()->getId()) {
            try {
                $this->_getUrlRewrite()->delete();
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    Mage::helper('Mage_Adminhtml_Helper_Data')->__('The URL Rewrite has been deleted.')
                );
            } catch (Exception $e) {
                $errorMessage =
                    Mage::helper('Mage_Adminhtml_Helper_Data')->__('An error occurred while deleting URL Rewrite.');
                Mage::getSingleton('Mage_Adminhtml_Model_Session')
                    ->addException($e, $errorMessage);
                $this->_redirect('*/*/edit/', array('id' => $this->_getUrlRewrite()->getId()));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Check whether this contoller is allowed in admin permissions
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Catalog::urlrewrite');
    }

    /**
     * Get Category from request
     *
     * @return Mage_Catalog_Model_Category
     */
    private function _getCategory()
    {
        if (!$this->_category) {
            $this->_category = Mage::getModel('Mage_Catalog_Model_Category');
            $categoryId = (int) $this->getRequest()->getParam('category', 0);

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
     * @return Mage_Catalog_Model_Product
     */
    private function _getProduct()
    {
        if (!$this->_product) {
            $this->_product = Mage::getModel('Mage_Catalog_Model_Product');
            $productId = (int) $this->getRequest()->getParam('product', 0);

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
     * @return Mage_Cms_Model_Page
     */
    private function _getCmsPage()
    {
        if (!$this->_cmsPage) {
            $this->_cmsPage = Mage::getModel('Mage_Cms_Model_Page');
            $cmsPageId = (int) $this->getRequest()->getParam('cms_page', 0);

            if (!$cmsPageId && $this->_getUrlRewrite()->getId()) {
                $urlRewriteId = $this->_getUrlRewrite()->getId();
                /** @var $cmsUrlRewrite Mage_Cms_Model_Page_Urlrewrite */
                $cmsUrlRewrite = Mage::getModel('Mage_Cms_Model_Page_Urlrewrite');
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
     * @return Mage_Core_Model_Url_Rewrite
     */
    private function _getUrlRewrite()
    {
        if (!$this->_urlRewrite) {
            $this->_urlRewrite = Mage::getModel('Mage_Core_Model_Url_Rewrite');

            $urlRewriteId = (int) $this->getRequest()->getParam('id', 0);
            if ($urlRewriteId) {
                $this->_urlRewrite->load((int) $this->getRequest()->getParam('id', 0));
            }
        }
        return $this->_urlRewrite;
    }
}
