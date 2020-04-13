<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Catalog\Controller\Adminhtml\Category;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Class Add Category
 *
 * @package Magento\Catalog\Controller\Adminhtml\Category
 */
class Add extends Category implements HttpGetActionInterface
{
    /**
     * Forward factory for result
     *
     * @deprecated Unused Class: ForwardFactory
     * @see $this->resultFactory->create()
     * @var ForwardFactory
     *
     */
    protected $resultForwardFactory;

    /**
     * Add category constructor
     *
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory
    ) {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
    }

    /**
     * Add new category form
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $parentId = (int)$this->getRequest()->getParam('parent');

        $category = $this->_initCategory(true);
        if (!$category || !$parentId || $category->getId()) {
            /** @var Redirect $resultRedirect */
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

        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        if ($this->getRequest()->getQuery('isAjax')) {
            return $this->ajaxRequestResponse($category, $resultPage);
        }

        $resultPage->setActiveMenu('Magento_Catalog::catalog_categories');
        $resultPage->getConfig()->getTitle()->prepend(__('New Category'));
        $resultPage->addBreadcrumb(__('Manage Catalog Categories'), __('Manage Categories'));

        return $resultPage;
    }
}
