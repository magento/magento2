<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Controller\Adminhtml\Category;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;

class CategoriesJson extends \Magento\Catalog\Controller\Adminhtml\Category implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        ?\Magento\Backend\Model\Auth\Session $authSession = null
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->authSession = $authSession ?: ObjectManager::getInstance()
            ->get(\Magento\Backend\Model\Auth\Session::class);
    }

    /**
     * Get tree node (Ajax version)
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('expand_all')) {
            $this->authSession->setIsTreeWasExpanded(true);
        } else {
            $this->authSession->setIsTreeWasExpanded(false);
        }
        $categoryId = (int)$this->getRequest()->getPost('id');

        // If categoryId is not provided, return an empty JSON response or an error
        if (!$categoryId) {
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setJsonData(json_encode(['error' => 'Category ID is required']));
        }

        if ($categoryId) {
            $this->getRequest()->setParam('id', $categoryId);

            $category = $this->_initCategory();
            if (!$category) {
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('catalog/*/', ['_current' => true, 'id' => null]);
            }
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setJsonData(
                $this->layoutFactory->create()->createBlock(\Magento\Catalog\Block\Adminhtml\Category\Tree::class)
                    ->getTreeJson($category)
            );
        }
    }
}
