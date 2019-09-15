<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Category;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Class Add Category
 *
 * @package Magento\Catalog\Controller\Adminhtml\Category
 */
class Add extends \Magento\Catalog\Controller\Adminhtml\Category implements HttpGetActionInterface
{
    /**
     * Forward factory for result
     *
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Add category constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
    }

    /**
     * Add new category form
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $parentId = (int)$this->getRequest()->getParam('parent');

        $category = $this->_initCategory(true);
        if (!$category || !$parentId || $category->getId()) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('catalog/*/', ['_current' => true, 'id' => null]);
        }

        /**
         * Check if there are data in session (if there was an exception on saving category)
         */
        $categoryData = $this->_getSession()->getCategoryData(true);
        if (is_array($categoryData)) {
            unset($categoryData['image']);
            $category->addData($categoryData);
        }

        $resultPageFactory = $this->_objectManager->get(\Magento\Framework\View\Result\PageFactory::class);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $resultPageFactory->create();

        if ($this->getRequest()->getQuery('isAjax')) {
            return $this->ajaxRequestResponse($category, $resultPage);
        }

        $resultPage->setActiveMenu('Magento_Catalog::catalog_categories');
        $resultPage->getConfig()->getTitle()->prepend(__('New Category'));
        $resultPage->addBreadcrumb(__('Manage Catalog Categories'), __('Manage Categories'));

        return $resultPage;
    }
}
