<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Controller\Adminhtml\Category;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\ObjectManager;

class Edit extends \Magento\Catalog\Controller\Adminhtml\Category implements HttpGetActionInterface
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
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        ?\Magento\Store\Model\StoreManagerInterface $storeManager = null
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()
            ->get(\Magento\Store\Model\StoreManagerInterface::class);
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
        $store = $this->storeManager->getStore($storeId);
        $this->storeManager->setCurrentStore($store->getCode());

        $categoryId = (int)$this->getRequest()->getParam('id');

        if (!$categoryId) {
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
        if (!$category || $categoryId != $category->getId() || !$category->getId()) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('catalog/*/', ['_current' => true, 'id' => null]);
        }

        /**
         * Check if there are data in session (if there was an exception on saving category)
         */
        $categoryData = $this->_getSession()->getCategoryData(true);
        if (is_array($categoryData)) {
            if (isset($categoryData['image']['delete'])) {
                $categoryData['image'] = null;
            } else {
                unset($categoryData['image']);
            }
            $category->addData($categoryData);
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        if ($this->getRequest()->getQuery('isAjax')) {
            return $this->ajaxRequestResponse($category, $resultPage);
        }

        $resultPageTitle = $categoryId ? $category->getName() . ' (ID: ' . $categoryId . ')' : __('Categories');
        $resultPage->setActiveMenu('Magento_Catalog::catalog_categories');
        $resultPage->getConfig()->getTitle()->prepend(__('Categories'));
        $resultPage->getConfig()->getTitle()->prepend($resultPageTitle);
        $resultPage->addBreadcrumb(__('Manage Catalog Categories'), __('Manage Categories'));

        return $resultPage;
    }
}
