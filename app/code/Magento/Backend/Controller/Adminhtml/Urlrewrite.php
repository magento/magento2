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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Urlrewrite extends Action
{
    /**
     * Default id mode key
     */
    const ID_MODE = 'id';

    /**
     * Product mode key
     */
    const PRODUCT_MODE = 'product';

    /**
     * Category mode key
     */
    const CATEGORY_MODE = 'category';

    /**
     * CMS mode key
     */
    const CMS_PAGE_MODE = 'cms_page';

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $cmsPage;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected $urlRewrite;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Backend\Block\Urlrewrite\Selector
     */
    protected $rewriteSelector;

    /**
     * @var \Magento\Backend\Block\Urlrewrite\Catalog\Category\Tree
     */
    protected $categoryTree;

    /**
     * @var \Magento\UrlRewrite\Helper\UrlRewrite
     */
    protected $rewriteHelper;

    /**
     * @var \Magento\Cms\Model\Page\UrlrewriteFactory
     */
    protected $cmsRewriteFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Magento\Catalog\Model\Url
     */
    protected $catalogUrl;

    /**
     * @var \Magento\Catalog\Model\Resource\UrlFactory
     */
    protected $catalogUrlFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Backend\Block\Urlrewrite\Selector $rewriteSelector
     * @param \Magento\Backend\Block\Urlrewrite\Catalog\Category\Tree $categoryTree
     * @param \Magento\UrlRewrite\Helper\UrlRewrite $rewriteHelper
     * @param \Magento\Cms\Model\Page\UrlrewriteFactory $cmsRewriteFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Catalog\Model\Url $catalogUrl
     * @param \Magento\Catalog\Model\Resource\UrlFactory $catalogUrlFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Backend\Block\Urlrewrite\Selector $rewriteSelector,
        \Magento\Backend\Block\Urlrewrite\Catalog\Category\Tree $categoryTree,
        \Magento\UrlRewrite\Helper\UrlRewrite $rewriteHelper,
        \Magento\Cms\Model\Page\UrlrewriteFactory $cmsRewriteFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Catalog\Model\Url $catalogUrl,
        \Magento\Catalog\Model\Resource\UrlFactory $catalogUrlFactory
    ) {
        $this->productFactory = $productFactory;
        $this->rewriteSelector = $rewriteSelector;
        $this->categoryTree = $categoryTree;
        $this->rewriteHelper = $rewriteHelper;
        $this->cmsRewriteFactory = $cmsRewriteFactory;
        $this->categoryFactory = $categoryFactory;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->pageFactory = $pageFactory;
        $this->catalogUrl = $catalogUrl;
        $this->catalogUrlFactory = $catalogUrlFactory;
        parent::__construct($context);
    }


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

        $mode = $this->getMode();

        switch ($mode) {
            case self::PRODUCT_MODE:
                $editBlock = $this->_view->getLayout()->createBlock(
                    'Magento\Backend\Block\Urlrewrite\Catalog\Product\Edit',
                    '',
                    array(
                        'data' => array(
                            'category' => $this->getCategory(),
                            'product' => $this->getProduct(),
                            'is_category_mode' => $this->getRequest()->has('category'),
                            'url_rewrite' => $this->getUrlRewrite()
                        )
                    )
                );
                break;
            case self::CATEGORY_MODE:
                $editBlock = $this->_view->getLayout()->createBlock(
                    'Magento\Backend\Block\Urlrewrite\Catalog\Category\Edit',
                    '',
                    array(
                        'data' => array('category' => $this->getCategory(), 'url_rewrite' => $this->getUrlRewrite())
                    )
                );
                break;
            case self::CMS_PAGE_MODE:
                $editBlock = $this->_view->getLayout()->createBlock(
                    'Magento\Backend\Block\Urlrewrite\Cms\Page\Edit',
                    '',
                    array(
                        'data' => array('cms_page' => $this->getCmsPage(), 'url_rewrite' => $this->getUrlRewrite())
                    )
                );
                break;
            case self::ID_MODE:
            default:
                $editBlock = $this->_view->getLayout()->createBlock(
                    'Magento\Backend\Block\Urlrewrite\Edit',
                    '',
                    array('data' => array('url_rewrite' => $this->getUrlRewrite()))
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
    protected function getMode()
    {
        if ($this->getProduct()->getId() || $this->getRequest()->has('product')) {
            $mode = self::PRODUCT_MODE;
        } elseif ($this->getCategory()->getId() || $this->getRequest()->has('category')) {
            $mode = self::CATEGORY_MODE;
        } elseif ($this->getCmsPage()->getId() || $this->getRequest()->has('cms_page')) {
            $mode = self::CMS_PAGE_MODE;
        } elseif ($this->getRequest()->has('id')) {
            $mode = self::ID_MODE;
        } else {
            $mode = $this->rewriteSelector->getDefaultMode();
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
        $this->getResponse()->setBody($this->categoryTree->getTreeArray($categoryId, true, 1));
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
        $data = $this->getRequest()->getPost();
        if ($data) {
            try {
                // set basic urlrewrite data
                /** @var $model \Magento\UrlRewrite\Model\UrlRewrite */
                $model = $this->getUrlRewrite();

                // Validate request path
                $requestPath = $this->getRequest()->getParam('request_path');
                $this->rewriteHelper->validateRequestPath($requestPath);

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

                $this->onUrlRewriteSaveBefore($model);

                // save and redirect
                $model->save();

                $this->onUrlRewriteSaveAfter($model);

                $this->messageManager->addSuccess(__('The URL Rewrite has been saved.'));
                $this->_redirect('adminhtml/*/');
                return;
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_session->setUrlrewriteData($data);
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('An error occurred while saving URL Rewrite.'));
                $this->_session->setUrlrewriteData($data);
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
    protected function onUrlRewriteSaveBefore($model)
    {
        $this->handleCatalogUrlRewrite($model);
        $this->handleCmsPageUrlRewrite($model);
    }

    /**
     * Call after save urlrewrite handlers
     *
     * @param \Magento\UrlRewrite\Model\UrlRewrite $model
     * @return void
     */
    protected function onUrlRewriteSaveAfter($model)
    {
        $this->handleCmsPageUrlRewriteSave($model);
    }

    /**
     * Override urlrewrite data, basing on current category and product
     *
     * @param \Magento\UrlRewrite\Model\UrlRewrite $model
     * @return void
     * @throws Exception
     */
    protected function handleCatalogUrlRewrite($model)
    {
        $product = $this->getInitializedProduct($model);
        $category = $this->getInitializedCategory($model);

        if ($product || $category) {
            $idPath = $this->catalogUrl->generatePath('id', $product, $category);
            $model->setIdPath($idPath);

            // if redirect specified try to find friendly URL
            $generateTarget = true;
            if ($this->rewriteHelper->hasRedirectOptions($model)) {
                /** @var $rewriteResource \Magento\Catalog\Model\Resource\Url */
                $rewriteResource = $this->catalogUrlFactory->create();
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
                $model->setTargetPath($this->catalogUrl->generatePath('target', $product, $category));
            }
        }
    }

    /**
     * Get product instance applicable for generatePath
     *
     * @param \Magento\UrlRewrite\Model\UrlRewrite $model
     * @return Product|null
     */
    protected function getInitializedProduct($model)
    {
        /** @var $product Product */
        $product = $this->getProduct();
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
    protected function getInitializedCategory($model)
    {
        /** @var $category Category */
        $category = $this->getCategory();
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
    protected function handleCmsPageUrlRewrite($model)
    {
        /** @var $cmsPage \Magento\Cms\Model\Page */
        $cmsPage = $this->getCmsPage();
        if (!$cmsPage->getId()) {
            return;
        }

        /** @var $cmsPageUrlRewrite \Magento\Cms\Model\Page\Urlrewrite */
        $cmsPageUrlRewrite = $this->cmsRewriteFactory->create();
        $idPath = $cmsPageUrlRewrite->generateIdPath($cmsPage);
        $model->setIdPath($idPath);

        // if redirect specified try to find friendly URL
        $generateTarget = true;
        if ($model->getId() && $this->rewriteHelper->hasRedirectOptions($model)) {
            /** @var $rewriteResource \Magento\Catalog\Model\Resource\Url */
            $rewriteResource = $this->catalogUrlFactory->create();
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
    protected function handleCmsPageUrlRewriteSave($model)
    {
        /** @var $cmsPage \Magento\Cms\Model\Page */
        $cmsPage = $this->getCmsPage();
        if (!$cmsPage->getId()) {
            return;
        }

        /** @var $cmsRewrite \Magento\Cms\Model\Page\Urlrewrite */
        $cmsRewrite = $this->cmsRewriteFactory->create();
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
        if ($this->getUrlRewrite()->getId()) {
            try {
                $this->getUrlRewrite()->delete();
                $this->messageManager->addSuccess(__('The URL Rewrite has been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('An error occurred while deleting URL Rewrite.'));
                $this->_redirect('adminhtml/*/edit/', array('id' => $this->getUrlRewrite()->getId()));
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
    protected function getCategory()
    {
        if (!$this->category) {
            $this->category = $this->categoryFactory->create();
            $categoryId = (int) $this->getRequest()->getParam('category', 0);

            if (!$categoryId && $this->getUrlRewrite()->getId()) {
                $categoryId = $this->getUrlRewrite()->getCategoryId();
            }

            if ($categoryId) {
                $this->category->load($categoryId);
            }
        }
        return $this->category;
    }

    /**
     * Get Product from request
     *
     * @return Product
     */
    protected function getProduct()
    {
        if (!$this->product) {
            $this->product = $this->productFactory->create();
            $productId = (int) $this->getRequest()->getParam('product', 0);

            if (!$productId && $this->getUrlRewrite()->getId()) {
                $productId = $this->getUrlRewrite()->getProductId();
            }

            if ($productId) {
                $this->product->load($productId);
            }
        }
        return $this->product;
    }

    /**
     * Get CMS page from request
     *
     * @return \Magento\Cms\Model\Page
     */
    protected function getCmsPage()
    {
        if (!$this->cmsPage) {
            $this->cmsPage = $this->pageFactory->create();
            $cmsPageId = (int) $this->getRequest()->getParam('cms_page', 0);

            if (!$cmsPageId && $this->getUrlRewrite()->getId()) {
                $urlRewriteId = $this->getUrlRewrite()->getId();
                /** @var $cmsUrlRewrite \Magento\Cms\Model\Page\Urlrewrite */
                $cmsUrlRewrite = $this->cmsRewriteFactory->create();
                $cmsUrlRewrite->load($urlRewriteId, 'url_rewrite_id');
                $cmsPageId = $cmsUrlRewrite->getCmsPageId();
            }

            if ($cmsPageId) {
                $this->cmsPage->load($cmsPageId);
            }
        }
        return $this->cmsPage;
    }

    /**
     * Get URL rewrite from request
     *
     * @return \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected function getUrlRewrite()
    {
        if (!$this->urlRewrite) {
            $this->urlRewrite = $this->urlRewriteFactory->create();

            $urlRewriteId = (int) $this->getRequest()->getParam('id', 0);
            if ($urlRewriteId) {
                $this->urlRewrite->load((int) $this->getRequest()->getParam('id', 0));
            }
        }
        return $this->urlRewrite;
    }
}
