<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Category;

class Edit extends \Magento\Catalog\Controller\Adminhtml\Category
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Edit constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Edit category page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        $parentId = (int)$this->getRequest()->getParam('parent');
        $categoryId = (int)$this->getRequest()->getParam('id');

        if (!$categoryId && !$parentId) {
            if ($storeId) {
                $categoryId = (int)$this->storeManager->getStore($storeId)->getRootCategoryId();
            } else {
                $defaultStoreView = $this->storeManager->getDefaultStoreView();
                if ($defaultStoreView) {
                    $categoryId = (int)$defaultStoreView->getRootCategoryId();
                } else {
                    $stores = $this->storeManager->getStores();
                    if (count($stores)) {
                        $store = reset($stores);
                        $categoryId = (int)$store->getRootCategoryId();
                    }
                }
            }
            $this->getRequest()->setParam('id', $categoryId);
        }

        $category = $this->_initCategory(true);
        if (!$category) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('catalog/*/', ['_current' => true, 'id' => null]);
        }

        /**
         * Check if we have data in session (if during category save was exception)
         */
        $data = $this->_getSession()->getCategoryData(true);
        if (isset($data['general'])) {
            $category->addData($data['general']);
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        /**
         * Build response for ajax request
         */
        if ($this->getRequest()->getQuery('isAjax')) {
            // prepare breadcrumbs of selected category, if any
            $breadcrumbsPath = $category->getPath();
            if (empty($breadcrumbsPath)) {
                // but if no category, and it is deleted - prepare breadcrumbs from path, saved in session
                $breadcrumbsPath = $this->_objectManager->get(
                    'Magento\Backend\Model\Auth\Session'
                )->getDeletedPath(
                    true
                );
                if (!empty($breadcrumbsPath)) {
                    $breadcrumbsPath = explode('/', $breadcrumbsPath);
                    // no need to get parent breadcrumbs if deleting category level 1
                    if (count($breadcrumbsPath) <= 1) {
                        $breadcrumbsPath = '';
                    } else {
                        array_pop($breadcrumbsPath);
                        $breadcrumbsPath = implode('/', $breadcrumbsPath);
                    }
                }
            }

            $eventResponse = new \Magento\Framework\DataObject([
                'content' => $resultPage->getLayout()->getUiComponent('category_form')->getFormHtml()
                    . $resultPage->getLayout()->getBlock('category.tree')
                        ->getBreadcrumbsJavascript($breadcrumbsPath, 'editingCategoryBreadcrumbs'),
                'messages' => $resultPage->getLayout()->getMessagesBlock()->getGroupedHtml(),
                'toolbar' => $resultPage->getLayout()->getBlock('page.actions.toolbar')->toHtml()
            ]);
            $this->_eventManager->dispatch(
                'category_prepare_ajax_response',
                ['response' => $eventResponse, 'controller' => $this]
            );
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setHeader('Content-type', 'application/json', true);
            $resultJson->setData($eventResponse->getData());
            return $resultJson;
        }

        $resultPage->setActiveMenu('Magento_Catalog::catalog_categories');
        $resultPage->getConfig()->getTitle()->prepend(__('Categories'));
        $resultPage->getConfig()->getTitle()->prepend($categoryId ? $category->getName() : __('Categories'));
        $resultPage->addBreadcrumb(__('Manage Catalog Categories'), __('Manage Categories'));

        $block = $resultPage->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($storeId);
        }

        return $resultPage;
    }
}
